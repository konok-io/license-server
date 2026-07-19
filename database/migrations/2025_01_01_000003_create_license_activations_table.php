<?php

declare(strict_types=1);

use App\Enums\ActivationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('license_id')
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Installation ID lock
            $table->string('installation_id', 191);
            $table->string('fingerprint_hash', 64)->nullable();

            // Domain lock
            $table->string('domain')->nullable();
            $table->string('normalized_domain')->nullable();
            $table->boolean('is_wildcard')->default(false);

            // Environment
            $table->string('server_type', 20)->nullable();  // localhost/domain/vps
            $table->string('ip_address', 45)->nullable();
            $table->string('os_info')->nullable();
            $table->string('user_agent')->nullable();

            $table->string('status')->default(ActivationStatus::Active->value);

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            // One installation may bind to a license only once (active uniqueness at app layer).
            $table->unique(['license_id', 'installation_id']);
            $table->index('normalized_domain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activations');
    }
};
