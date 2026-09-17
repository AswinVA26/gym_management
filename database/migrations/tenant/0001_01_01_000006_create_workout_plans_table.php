<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('focus')->nullable();
            $table->string('level')->nullable();
            $table->unsignedTinyInteger('days_per_week')->default(3);
            $table->longText('plan');
            $table->enum('generated_by', ['ai', 'manual'])->default('ai');
            $table->json('ai_meta')->nullable();
            $table->timestamps();

            $table->index('generated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
