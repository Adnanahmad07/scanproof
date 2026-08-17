<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->default('cleaning');
            $table->string('priority')->default('medium');
            $table->string('frequency')->default('daily'); // daily, weekly, monthly
            $table->unsignedInteger('day_of_week')->nullable(); // 0=Sunday, 6=Saturday
            $table->unsignedInteger('day_of_month')->nullable(); // 1-31
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');
    }
};
