<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable, append-only audit trail with optional hash-chaining
     * (previous_hash + hash) for tamper-evidence.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('event', 60);           // App\Enums\AuditEvent

            // Polymorphic subject (license, activation, customer, ...)
            $table->nullableMorphs('auditable');

            // Actor
            $table->string('actor_type', 40)->nullable();   // admin / system / api_client
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('meta')->nullable();

            // Tamper-evident hash chain
            $table->string('previous_hash', 64)->nullable();
            $table->string('hash', 64)->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('event');
            $table->index(['actor_type', 'actor_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
