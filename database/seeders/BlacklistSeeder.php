<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\License;
use App\Models\LicenseBlacklist;
use Illuminate\Database\Seeder;

class BlacklistSeeder extends Seeder
{
    public function run(): void
    {
        // Blacklist a small random sample of existing licenses.
        License::query()->inRandomOrder()->limit(3)->get()
            ->each(function (License $license): void {
                $entry = LicenseBlacklist::factory()->for($license)->create([
                    'license_key_hash' => $license->license_key_hash,
                ]);

                AuditLog::factory()->create([
                    'event'          => AuditEvent::Blacklisted->value,
                    'auditable_type' => LicenseBlacklist::class,
                    'auditable_id'   => $entry->id,
                    'actor_type'     => 'admin',
                    'description'    => "License #{$license->id} blacklisted: {$entry->reason}",
                ]);
            });

        // One lifted (historical) blacklist entry.
        LicenseBlacklist::factory()->lifted()->create();
    }
}
