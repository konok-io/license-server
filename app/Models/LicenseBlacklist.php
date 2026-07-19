<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LicenseBlacklist extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseBlacklistFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'license_id',
        'installation_id',
        'normalized_domain',
        'ip_address',
        'license_key_hash',
        'reason',
        'is_active',
        'created_by',
        'created_by_name',
        'blacklisted_at',
        'lifted_at',
        'meta',
    ];

    protected $hidden = [
        'license_key_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'blacklisted_at' => 'datetime',
            'lifted_at'      => 'datetime',
            'meta'          => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LicenseBlacklist $entry): void {
            $entry->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<License, LicenseBlacklist> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /** @param Builder<LicenseBlacklist> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
