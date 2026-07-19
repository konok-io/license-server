<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * audit:reseal
 * ------------
 * Recomputes the hash chain for every audit_logs row using the current
 * hashing formula, in id order. Use this ONCE after changing the hash
 * formula (e.g. the timestamp-precision fix) so previously written rows —
 * which were sealed with the old formula — stop showing as "tampered".
 *
 * This is a maintenance/migration tool. It intentionally bypasses the model's
 * immutability guard by writing straight to the query builder. Only an admin
 * running locally should use it; it does not weaken runtime tamper-evidence
 * (new rows are still chained normally on creation).
 */
class ResealAuditChain extends Command
{
    protected $signature = 'audit:reseal {--force : Skip the confirmation prompt}';

    protected $description = 'Recompute the audit log hash chain with the current formula.';

    public function handle(): int
    {
        $count = AuditLog::query()->count();

        if ($count === 0) {
            $this->info('No audit log entries to reseal.');

            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm("Reseal the hash chain for {$count} audit entries?")) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        $previousHash = null;
        $resealed = 0;

        // Ordered by id so the chain is rebuilt in the original write order.
        AuditLog::query()->orderBy('id')->chunkById(200, function ($logs) use (&$previousHash, &$resealed) {
            foreach ($logs as $log) {
                $log->previous_hash = $previousHash;
                $newHash = $log->computeHash();

                // Direct write — bypasses the immutable updating() guard.
                DB::table($log->getTable())
                    ->where('id', $log->id)
                    ->update([
                        'previous_hash' => $previousHash,
                        'hash'          => $newHash,
                    ]);

                $previousHash = $newHash;
                $resealed++;
            }
        });

        $this->info("Resealed {$resealed} audit entries. The chain is now intact.");

        return self::SUCCESS;
    }
}
