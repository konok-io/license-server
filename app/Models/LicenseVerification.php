<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseVerification extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseVerificationFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'license_activation_id',
        'result',
        'installation_id',
        'normalized_domain',
        'ip_address',
        'nonce',
        'payload_hash',
        'latency_ms',
        'verified_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'result'      => VerificationResult::class,
            'latency_ms'  => 'integer',
            'verified_at' => 'datetime',
            'meta'        => 'array',
        ];
    }

    /** @return BelongsTo<License, LicenseVerification> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /** @return BelongsTo<LicenseActivation, LicenseVerification> */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(LicenseActivation::class, 'license_activation_id');
    }
}
