<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_blacklists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // A blacklist entry may target a license, an installation, a domain, or an IP.
            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('installation_id', 191)->nullable();
            $table->string('normalized_domain')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('license_key_hash', 64)->nullable();

            $table->string('reason');
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamp('blacklisted_at')->nullable();
            $table->timestamp('lifted_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('installation_id');
            $table->index('normalized_domain');
            $table->index('ip_address');
            $table->index('license_key_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_blacklists');
    }
};
