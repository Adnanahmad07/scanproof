<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('configured')->default(false)->after('notes');
            $table->foreignId('configured_by')->nullable()->after('configured')->constrained('users')->nullOnDelete();
            $table->timestamp('configured_at')->nullable()->after('configured_by');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['configured_by']);
            $table->dropColumn(['configured', 'configured_by', 'configured_at']);
        });
    }
};
