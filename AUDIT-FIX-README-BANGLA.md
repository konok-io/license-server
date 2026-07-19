# লাইসেন্স সার্ভার — Audit Chain "tampered" ফিক্স (বাংলা)

## কী ঠিক করা হয়েছে

অডিট ট্রেইলে `license.activated` এন্ট্রি "tampered" (লাল) দেখাচ্ছিল। এটা কোনো
নিরাপত্তা সমস্যা বা activate ব্যর্থতা ছিল না — একটা timestamp precision bug।

কারণ: অডিট রেকর্ডের hash তৈরি হতো microsecond/timezone-offset সহ timestamp দিয়ে
(`toIso8601String()`), কিন্তু ডাটাবেজের `created_at` কলাম শুধু সেকেন্ড precision
রাখে। ফলে রেকর্ড পড়ার সময় hash আবার হিসাব করলে timestamp মিলত না → "tampered"।

ফিক্স (app/Models/AuditLog.php):
1. রেকর্ড তৈরির সময় `now()->startOfSecond()` — সেকেন্ড precision-এ pin।
2. hash-এ `format('Y-m-d H:i:s')` — save আর reload দুই দিকেই একই থাকে।

এখন নতুন সব এন্ট্রি সবুজ (chain intact) দেখাবে।

## পুরনো এন্ট্রি ঠিক করা (একবারই)

ফিক্সের আগে তৈরি পুরনো এন্ট্রিগুলো (যেমন আপনার এখনকার `license.issued` /
`license.activated`) পুরনো ফর্মুলায় sealed। ফিক্সের পরও ওগুলো লাল দেখাবে, কারণ
নতুন ফর্মুলায় ওদের hash মেলে না। এগুলো ঠিক করতে একবার এই কমান্ড দিন:

```bash
php artisan audit:reseal --force
```

এটা সব পুরনো এন্ট্রির hash চেইন নতুন ফর্মুলায় পুনরায় হিসাব করে দেয় — এরপর
সব এন্ট্রি সবুজ দেখাবে।

> নোট: এটা একটা maintenance কমান্ড, একবারই দরকার। নতুন এন্ট্রি স্বাভাবিকভাবেই
> সঠিক chain-এ তৈরি হবে, তাই ভবিষ্যতে আর লাগবে না।

## বিকল্প — টেস্ট ডেটা হলে

আপনি যদি এখনো ডেভেলপমেন্ট/টেস্ট পর্যায়ে থাকেন আর পুরনো অডিট লগ রাখার দরকার না হয়,
তাহলে reseal-এর বদলে audit_logs টেবিল খালি করেও দিতে পারেন (নতুন করে activate
করলে সবুজ চেইন তৈরি হবে):

```bash
php artisan tinker
>>> \Illuminate\Support\Facades\DB::table('audit_logs')->truncate();
```

## ইনস্টলের পর

স্বাভাবিক সার্ভার সেটআপ কমান্ডের পর (composer install, migrate, key:generate,
RSA key generate ইত্যাদি), শুধু একবার `php artisan audit:reseal --force` দিলেই
পুরনো tampered চিহ্ন চলে যাবে।
