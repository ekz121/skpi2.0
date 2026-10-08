<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('admin_checked_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_checked_at')->nullable()->after('admin_checked_by')->index();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['admin_checked_by']);
            $table->dropColumn(['admin_checked_by', 'admin_checked_at']);
        });
    }
};
