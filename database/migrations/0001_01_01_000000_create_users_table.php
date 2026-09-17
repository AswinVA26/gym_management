<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central (platform) schema.
     *
     * Hosts tenant registry records and the application's authentication
     * users (platform admins + every gym's staff). All gym business data
     * lives inside each tenant's own database.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('db_name')->unique();
            $table->string('db_host')->nullable();
            $table->unsignedInteger('db_port')->nullable();
            $table->string('db_username')->nullable();
            $table->text('db_password')->nullable();
            $table->json('settings')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('role', ['super_admin', 'gym_admin', 'trainer', 'member'])
                ->default('gym_admin');

            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('role');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tenants');
    }
};
