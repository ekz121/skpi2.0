<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE skpi_requests MODIFY status ENUM('pending','revision','issued','failed','rejected') NOT NULL DEFAULT 'pending'");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('skpi_requests', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }

        Schema::table('skpi_requests', function (Blueprint $table) {
            $table->string('docx_path')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        DB::table('skpi_requests')->where('status', 'rejected')->update(['status' => 'revision']);

        Schema::table('skpi_requests', function (Blueprint $table) {
            $table->dropColumn('docx_path');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE skpi_requests MODIFY status ENUM('pending','revision','issued','failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
