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
            DB::statement("ALTER TABLE skpi_requests MODIFY status ENUM('pending','revision','issued','failed','rejected','withdrawn') NOT NULL DEFAULT 'pending'");
        }

        Schema::create('skpi_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skpi_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->string('document_number')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamp('withdrawn_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skpi_withdrawals');
        DB::table('skpi_requests')->where('status', 'withdrawn')->update(['status' => 'rejected']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE skpi_requests MODIFY status ENUM('pending','revision','issued','failed','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
