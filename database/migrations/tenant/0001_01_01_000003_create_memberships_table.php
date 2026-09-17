<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
