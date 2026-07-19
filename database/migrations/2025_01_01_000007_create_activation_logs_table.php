<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('license_activation_id')
                ->nullable()
                ->constrained('license_activations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('action', 40);          // activate / deactivate / reactivate / denied
            $table->boolean('success')->default(true);
            $table->string('reason')->nullable();

            $table->string('installation_id', 191)->nullable();
            $table->string('normalized_domain')->nullable();
            $table->string('server_type', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->json('request_payload')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'created_at']);
            $table->index('action');
            $table->index('success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_logs');
    }
};
