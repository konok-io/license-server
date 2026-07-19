<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Models\License;
use App\Support\AuditLogger;
use Illuminate\Console\Command;

/**
 * Marks active licenses whose expiry (plus grace) has passed as Expired.
 * Runs daily; complements the client-side grace handling in verification.
 */
class ExpireLicenses extends Command
{
    protected $signature = 'license:expire {--dry-run : Report without writing}';

    protected $description = 'Expire licenses whose validity + grace window has elapsed.';

    public function handle(): int
    {
        $now = now();
        $expired = 0;

        License::query()
            ->where('status', LicenseStatus::Active->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->chunkById(200, function ($licenses) use ($now, &$expired): void {
                foreach ($licenses as $license) {
                    $graceEnds = $license->expires_at->copy()->addDays((int) $license->grace_days);
                    if ($now->lessThanOrEqualTo($graceEnds)) {
                        continue; // still within grace
                    }

                    if ($this->option('dry-run')) {
                        $this->line("Would expire: {$license->license_key_prefix}… (expired {$license->expires_at->toDateString()})");
                        $expired++;
                        continue;
                    }

                    $license->forceFill(['status' => LicenseStatus::Expired->value])->save();
                    AuditLogger::record(
                        AuditEvent::LicenseExpired,
                        $license,
                        'License auto-expired by scheduler',
                        actorType: 'system',
                    );
                    $expired++;
                }
            });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '') . "Expired {$expired} license(s).");

        return self::SUCCESS;
    }
}
