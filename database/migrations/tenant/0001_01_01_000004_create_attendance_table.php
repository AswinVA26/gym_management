<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'check_in']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
