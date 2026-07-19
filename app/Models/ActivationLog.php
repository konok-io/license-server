<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivationLogFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'license_activation_id',
        'action',
        'success',
        'reason',
        'installation_id',
        'normalized_domain',
        'server_type',
        'ip_address',
        'user_agent',
        'request_payload',
    ];

    protected function casts(): array
    {
        return [
            'success'         => 'boolean',
            'request_payload' => 'array',
        ];
    }

    /** @return BelongsTo<License, ActivationLog> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /** @return BelongsTo<LicenseActivation, ActivationLog> */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(LicenseActivation::class, 'license_activation_id');
    }
}
