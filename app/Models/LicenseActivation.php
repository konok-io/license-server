<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LicenseActivation extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseActivationFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'license_id',
        'installation_id',
        'fingerprint_hash',
        'domain',
        'normalized_domain',
        'is_wildcard',
        'server_type',
        'ip_address',
        'os_info',
        'user_agent',
        'status',
        'activated_at',
        'last_verified_at',
        'revoked_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status'           => ActivationStatus::class,
            'is_wildcard'      => 'boolean',
            'activated_at'     => 'datetime',
            'last_verified_at' => 'datetime',
            'revoked_at'       => 'datetime',
            'meta'             => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LicenseActivation $activation): void {
            $activation->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<License, LicenseActivation> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /** @return HasMany<LicenseVerification> */
    public function verifications(): HasMany
    {
        return $this->hasMany(LicenseVerification::class);
    }

    /** @param Builder<LicenseActivation> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', ActivationStatus::Active);
    }
}
