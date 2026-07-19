<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationLogFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'license_verification_id',
        'result',
        'kill_directive',
        'installation_id',
        'normalized_domain',
        'ip_address',
        'user_agent',
        'nonce',
        'latency_ms',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'kill_directive'   => 'boolean',
            'latency_ms'       => 'integer',
            'request_payload'  => 'array',
            'response_payload' => 'array',
        ];
    }

    /** @return BelongsTo<License, VerificationLog> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /** @return BelongsTo<LicenseVerification, VerificationLog> */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(LicenseVerification::class, 'license_verification_id');
    }
}
