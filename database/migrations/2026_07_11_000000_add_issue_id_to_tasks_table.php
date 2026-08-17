<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->index('issue_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['issue_id']);
            $table->dropIndex(['issue_id']);
            $table->dropColumn('issue_id');
        });
    }
};
