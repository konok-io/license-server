<?php

declare(strict_types=1);

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Plaintext key is never stored. Encrypted store + hash for O(1) lookup.
            $table->text('license_key_encrypted');
            $table->string('license_key_hash', 64)->unique();       // SHA-256 hex
            $table->string('license_key_prefix', 16)->index();      // display only e.g. SLS-XXXX

            $table->string('product', 100)->default('saudi-manpower-erp');
            $table->string('version', 20)->nullable();

            $table->string('type')->default(LicenseType::Domain->value);
            $table->string('status')->default(LicenseStatus::Pending->value);

            // Binding constraints
            $table->unsignedInteger('max_activations')->default(1);
            $table->unsignedInteger('activation_count')->default(0);

            // RSA signature material (Phase 2 crypto)
            $table->string('rsa_key_version', 20)->nullable();
            $table->text('rsa_signature')->nullable();

            // Kill switch + lifecycle
            $table->boolean('kill_switch')->default(false);
            $table->unsignedSmallInteger('grace_days')->default(3);
            $table->unsignedSmallInteger('verification_interval_hours')->default(24);

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('killed_at')->nullable();

            $table->json('features')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index('kill_switch');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
