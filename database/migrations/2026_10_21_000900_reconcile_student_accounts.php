<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $profiles = DB::table('student_profiles')
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->join('study_programs', 'study_programs.id', '=', 'student_profiles.study_program_id')
            ->where('users.role', 'student')
            ->select([
                'users.id as user_id',
                'student_profiles.nim',
                'student_profiles.study_program_id',
                'study_programs.faculty_id',
            ])
            ->get();

        foreach ($profiles as $profile) {
            DB::table('users')->where('id', $profile->user_id)->update([
                'study_program_id' => $profile->study_program_id,
                'faculty_id' => $profile->faculty_id,
                'updated_at' => now(),
            ]);

            $alreadyLinked = DB::table('student_registries')
                ->where('user_id', $profile->user_id)
                ->exists();

            if (! $alreadyLinked) {
                DB::table('student_registries')
                    ->where('nim', $profile->nim)
                    ->whereNull('user_id')
                    ->update([
                        'user_id' => $profile->user_id,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Rekonsiliasi tidak dibalik agar relasi akun produksi tidak terputus.
    }
};
