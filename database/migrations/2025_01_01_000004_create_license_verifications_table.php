<?php

declare(strict_types=1);

use App\Enums\VerificationResult;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('license_activation_id')
                ->nullable()
                ->constrained('license_activations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('result')->default(VerificationResult::Success->value);
            $table->string('installation_id', 191)->nullable();
            $table->string('normalized_domain')->nullable();
            $table->string('ip_address', 45)->nullable();

            // Replay protection + integrity
            $table->string('nonce', 64)->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'verified_at']);
            $table->index('result');
            $table->index('nonce');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_verifications');
    }
};
