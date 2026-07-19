<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'company',
        'email',
        'phone',
        'country',
        'is_active',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta'      => 'array',
        ];
    }

    /**
     * Auto-assign a UUID on creation without relying on the HasUuids
     * primary-key convention (we keep an auto-increment id).
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            $customer->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    /** @return HasMany<License> */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
