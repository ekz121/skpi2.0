<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150)->unique();
            $table->timestamps();
        });

        $facultyId = DB::table('faculties')->insertGetId([
            'code' => 'POLTEKSI',
            'name' => 'Politeknik Semen Indonesia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('study_programs', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
        DB::table('study_programs')->update(['faculty_id' => $facultyId]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student','program_verifier','admin','leader') NOT NULL DEFAULT 'student'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('study_program_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('study_program_id')->index();
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
        });

        DB::table('users')->update(['faculty_id' => $facultyId]);
        DB::table('student_profiles')
            ->select(['user_id', 'study_program_id'])
            ->orderBy('id')
            ->get()
            ->each(fn ($profile) => DB::table('users')->where('id', $profile->user_id)->update([
                'study_program_id' => $profile->study_program_id,
            ]));
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropForeign(['study_program_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['faculty_id', 'study_program_id', 'is_active', 'deactivated_at', 'deactivated_by']);
        });

        DB::table('users')->whereNotIn('role', ['student', 'admin'])->update(['role' => 'admin']);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student','admin') NOT NULL DEFAULT 'student'");
        }

        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn('faculty_id');
        });
        Schema::dropIfExists('faculties');
    }
};
