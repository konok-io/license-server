# MRH License Server — Installation (v3, Breeze-free)

Install path উদাহরণ: `C:\laragon\www\saudi-license-server`
Requirements: PHP 8.3+, Composer, MySQL 8+ (Laragon/XAMPP), OpenSSL.
**Node.js বা npm লাগবে না** — সব UI CDN Bootstrap ব্যবহার করে।

---

## এক নজরে সম্পূর্ণ কমান্ড

```bash
cd C:/laragon/www/saudi-license-server

composer install
cp .env.example .env
php artisan key:generate

# MySQL-এ ডেটাবেস বানান (Laragon-এ সাধারণত root, পাসওয়ার্ড খালি):
#   phpMyAdmin/HeidiSQL-এ 'saudi_license_server' নামে খালি DB বানান
# অথবা Laragon menu > MySQL > কমান্ড দিয়ে।

php artisan license:generate-keys --bits=4096
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

তারপর ব্রাউজারে: **http://127.0.0.1:8000**

**Login:**
```
Email:    admin@saudilicense.local
Password: ChangeMe#2026
```

---

## ধাপে ধাপে

### ১. Files
জিপ extract করুন প্রজেক্ট ফোল্ডারে।

### ২. Dependencies
```bash
composer install
```
> `vendor/` বানাবে (ইন্টারনেট লাগবে)। **Breeze/npm লাগবে না।**

### ৩. Environment
```bash
cp .env.example .env
php artisan key:generate
```
`.env`-এ ডেটাবেস মিলিয়ে নিন (Laragon default: `DB_USERNAME=root`, `DB_PASSWORD=` খালি)।

### ৪. Database
`saudi_license_server` নামে একটা খালি ডেটাবেস বানান।

### ৫. RSA keys
```bash
php artisan license:generate-keys --bits=4096
```

### ৬. Migrate + Seed
```bash
php artisan migrate --seed
```
> টেবিল + roles/permissions + admin user + demo data তৈরি করে।

### ৭. Storage link + চালু
```bash
php artisan storage:link
php artisan serve
```

---

## URL গুলো

**Admin panel:**
```
http://127.0.0.1:8000/login   → login
http://127.0.0.1:8000/admin   → dashboard (login-এর পর)
```

**API (ERP client):**
```
POST /api/activate
POST /api/verify
POST /api/check-domain
POST /api/check-installation
POST /api/reset
```

---

## Production checklist
- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false`
- [ ] নতুন RSA keys (`license:generate-keys --force`)
- [ ] Admin password পরিবর্তন (`.env`-এ `ADMIN_PASSWORD` বা login-এর পর)
- [ ] HTTPS
- [ ] Apache/Nginx DocumentRoot → `public/`
- [ ] `php artisan config:cache route:cache view:cache`
- [ ] Scheduler cron: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`

---

## v3-তে যা বদলেছে (গুরুত্বপূর্ণ)
- **Breeze সম্পূর্ণ বাদ।** Login/logout এখন নিজস্ব `AuthController` + CDN Bootstrap view দিয়ে — **কোনো Vite/Tailwind/npm build লাগে না**। এতে আগের সব সমস্যা (View [welcome] not found, Vite manifest not found, CSS লোড না হওয়া) একবারে সমাধান।
- Root `/` redirect ও admin routing `bootstrap/app.php`-এ (overwrite-proof)।
- সব global class (`\Throwable`, `\RuntimeException`, `\Closure`) সঠিকভাবে ব্যবহৃত — কোনো import warning নেই।
- `APP_ENV=local`, `APP_DEBUG=true` (প্রথম সেটআপে সুবিধা)।

---

## সমস্যা?
- **500 error** → `.env`-এ `APP_DEBUG=true` করে আসল error দেখুন, বা `tail -50 storage/logs/laravel.log`
- **Class not found** → `composer dump-autoload`
- **Route/config সমস্যা** → `php artisan optimize:clear`
- **Login কাজ করে না** → `php artisan migrate --seed` চালিয়েছেন কিনা দেখুন (admin user দরকার)
- **storage permission** → `storage/` ও `bootstrap/cache/` writable রাখুন

---

## ডেমো ডেটা (ঐচ্ছিক)

ডিফল্টে `migrate --seed` শুধু permissions, roles, ও admin user বানায় — **কোনো ডেমো ডেটা নেই**, সিস্টেম খালি ও production-ready।

টেস্টের জন্য নমুনা customer/license/log যোগ করতে চাইলে:
```bash
php artisan db:seed --class=DemoDataSeeder
```

ডেমো ডেটা সরিয়ে আবার খালি করতে (admin/permissions রেখে):
```bash
php artisan tinker --execute="
Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
foreach(['verification_logs','activation_logs','license_verifications','license_resets','license_activations','license_blacklists','licenses','customers','audit_logs'] as \$t) {
    Illuminate\Support\Facades\DB::table(\$t)->truncate();
}
Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
echo 'ডেমো ডেটা সরানো হলো';
"
```
