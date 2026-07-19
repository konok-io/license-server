<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class License extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'license_key_encrypted',
        'license_key_hash',
        'license_key_prefix',
        'product',
        'version',
        'type',
        'status',
        'max_activations',
        'activation_count',
        'rsa_key_version',
        'rsa_signature',
        'kill_switch',
        'grace_days',
        'verification_interval_hours',
        'issued_at',
        'starts_at',
        'expires_at',
        'last_verified_at',
        'killed_at',
        'features',
        'meta',
    ];

    protected $hidden = [
        'license_key_encrypted',
        'license_key_hash',
        'rsa_signature',
    ];

    protected function casts(): array
    {
        return [
            'type'                        => LicenseType::class,
            'status'                      => LicenseStatus::class,
            'max_activations'             => 'integer',
            'activation_count'            => 'integer',
            'kill_switch'                 => 'boolean',
            'grace_days'                  => 'integer',
            'verification_interval_hours' => 'integer',
            'issued_at'                   => 'datetime',
            'starts_at'                   => 'datetime',
            'expires_at'                  => 'datetime',
            'last_verified_at'            => 'datetime',
            'killed_at'                   => 'datetime',
            'features'                    => 'array',
            'meta'                        => 'array',
            // Encrypted at rest — plaintext never persisted.
            'license_key_encrypted'       => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (License $license): void {
            $license->uuid ??= (string) Str::uuid();
        });
    }

    /* ----------------------------------------------------------------
     | Relationships
     * ---------------------------------------------------------------- */

    /** @return BelongsTo<Customer, License> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasMany<LicenseActivation> */
    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    /** @return HasMany<LicenseVerification> */
    public function verifications(): HasMany
    {
        return $this->hasMany(LicenseVerification::class);
    }

    /** @return HasMany<LicenseReset> */
    public function resets(): HasMany
    {
        return $this->hasMany(LicenseReset::class);
    }

    /** @return HasMany<LicenseBlacklist> */
    public function blacklists(): HasMany
    {
        return $this->hasMany(LicenseBlacklist::class);
    }

    /** @return HasMany<ActivationLog> */
    public function activationLogs(): HasMany
    {
        return $this->hasMany(ActivationLog::class);
    }

    /** @return HasMany<VerificationLog> */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }

    /* ----------------------------------------------------------------
     | Scopes
     * ---------------------------------------------------------------- */

    /** @param Builder<License> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', LicenseStatus::Active);
    }

    /** @param Builder<License> $query */
    public function scopeKilled(Builder $query): void
    {
        $query->where('kill_switch', true);
    }

    /** @param Builder<License> $query */
    public function scopeForKeyHash(Builder $query, string $hash): void
    {
        $query->where('license_key_hash', $hash);
    }

    /* ----------------------------------------------------------------
     | Domain helpers
     * ---------------------------------------------------------------- */

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasAvailableActivationSlot(): bool
    {
        return $this->activation_count < $this->max_activations;
    }

    public function isUsable(): bool
    {
        return $this->status->isUsable()
            && ! $this->kill_switch
            && ! $this->isExpired();
    }
}
