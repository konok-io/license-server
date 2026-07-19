<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('license_verification_id')
                ->nullable()
                ->constrained('license_verifications')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('result', 40);
            $table->boolean('kill_directive')->default(false);
            $table->string('installation_id', 191)->nullable();
            $table->string('normalized_domain')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->string('nonce', 64)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'created_at']);
            $table->index('result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
