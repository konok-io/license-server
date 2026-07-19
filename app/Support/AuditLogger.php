<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Thin helper to append immutable, hash-chained audit records.
 * Services call this rather than touching AuditLog directly.
 */
class AuditLogger
{
    public static function record(
        AuditEvent $event,
        ?Model $auditable = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null,
        string $actorType = 'admin',
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'event'          => $event->value,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id'   => $auditable?->getKey(),
            'actor_type'     => $user !== null ? $actorType : 'system',
            'actor_id'       => $user?->getAuthIdentifier(),
            'actor_name'     => $user->name ?? 'System',
            'ip_address'     => Request::ip(),
            'user_agent'     => Str::limit((string) Request::userAgent(), 500, ''),
            'description'    => $description,
            'old_values'    => $oldValues,
            'new_values'    => $newValues,
            'meta'          => $meta,
        ]);
    }
}
