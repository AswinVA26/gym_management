<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
