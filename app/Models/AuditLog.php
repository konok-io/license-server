<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AuditEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Immutable, append-only audit trail.
 *
 * Records are hash-chained: each row stores the previous row's hash and a
 * hash computed over its own canonical payload, providing tamper-evidence.
 * Updates and deletes are blocked at the model layer.
 */
class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null; // append-only: no updated_at column

    protected $fillable = [
        'uuid',
        'event',
        'auditable_type',
        'auditable_id',
        'actor_type',
        'actor_id',
        'actor_name',
        'ip_address',
        'user_agent',
        'description',
        'old_values',
        'new_values',
        'meta',
        'previous_hash',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'event'      => AuditEvent::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'meta'       => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log): void {
            $log->uuid ??= (string) Str::uuid();
            // Pin to second precision so the value used to compute the hash is
            // identical to what the database stores and later reloads. Using a
            // microsecond/offset-bearing timestamp here caused the recomputed
            // hash on read to differ, which the admin UI flagged as "tampered".
            $log->created_at ??= now()->startOfSecond();

            // Chain to the most recent record.
            $previous = static::query()->latest('id')->first();
            $log->previous_hash = $previous?->hash;
            $log->hash = $log->computeHash();
        });

        // Enforce immutability.
        static::updating(function (): void {
            throw new \RuntimeException('Audit logs are immutable and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Audit logs are immutable and cannot be deleted.');
        });
    }

    public function computeHash(): string
    {
        $payload = json_encode([
            'event'          => $this->event instanceof AuditEvent ? $this->event->value : $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id'   => $this->auditable_id,
            'actor_type'     => $this->actor_type,
            'actor_id'       => $this->actor_id,
            'old_values'     => $this->old_values,
            'new_values'     => $this->new_values,
            // Fixed second-precision, timezone-stable format. This must match
            // exactly whether the model was just created or reloaded from the
            // database (where the column has no sub-second precision).
            'created_at'     => optional($this->created_at)->format('Y-m-d H:i:s'),
            'previous_hash'  => $this->previous_hash,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', (string) $payload);
    }

    /** Verify this record's integrity against the chain. */
    public function isChainValid(): bool
    {
        return hash_equals($this->hash ?? '', $this->computeHash());
    }

    /** @return MorphTo<Model, AuditLog> */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
