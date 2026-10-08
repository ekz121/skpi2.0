<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->string('national_code', 10)->nullable()->unique()->after('code');
            $table->string('academic_title', 30)->nullable()->after('name');
        });

        $programs = [
            'TI' => ['national_code' => '57403', 'academic_title' => 'A.Md.Kom.'],
            'TM' => ['national_code' => '21401', 'academic_title' => 'A.Md.T.'],
            'AP' => ['national_code' => '61401', 'academic_title' => 'A.Md.A.B.'],
            'AK' => ['national_code' => '62401', 'academic_title' => 'A.Md.Ak.'],
        ];
        foreach ($programs as $code => $values) {
            DB::table('study_programs')->where('code', $code)->update($values + ['updated_at' => now()]);
        }

        Schema::create('student_registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nim', 30)->unique();
            $table->string('name', 150)->index();
            $table->year('cohort')->default(2023);
            $table->year('graduation_year')->default(2026);
            $table->string('diploma_number', 100)->unique();
            $table->timestamps();
        });

        Schema::table('skpi_requests', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });

        DB::table('users')->where('email', 'admin@polteksi.ac.id')->where('role', 'admin')->update([
            'password' => Hash::make('pastikerja123'),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('email', 'mahasiswa@demo.polteksi.ac.id')->where('role', 'student')->delete();
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@polteksi.ac.id')->where('role', 'admin')->update([
            'password' => Hash::make('polteksi123'),
            'updated_at' => now(),
        ]);

        Schema::table('skpi_requests', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('document_number');
        });

        Schema::dropIfExists('student_registries');

        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropUnique(['national_code']);
            $table->dropColumn(['national_code', 'academic_title']);
        });
    }
};
