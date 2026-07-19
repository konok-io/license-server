<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_resets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('license_id')
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('reason')->nullable();
            $table->unsignedInteger('activations_cleared')->default(0);

            // Signature rotation snapshot
            $table->string('old_rsa_key_version', 20)->nullable();
            $table->string('new_rsa_key_version', 20)->nullable();

            // Actor (admin user) — kept nullable to avoid hard dependency on users table in Phase 2.
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('performed_by_name')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('reset_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'reset_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_resets');
    }
};
