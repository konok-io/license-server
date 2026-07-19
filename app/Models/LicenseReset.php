<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LicenseReset extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseResetFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'license_id',
        'reason',
        'activations_cleared',
        'old_rsa_key_version',
        'new_rsa_key_version',
        'performed_by',
        'performed_by_name',
        'ip_address',
        'reset_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'activations_cleared' => 'integer',
            'reset_at'            => 'datetime',
            'meta'               => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LicenseReset $reset): void {
            $reset->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<License, LicenseReset> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
