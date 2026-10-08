<?php

namespace Database\Seeders;

use App\Models\StudentRegistry;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudentRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $programs = StudyProgram::whereNotNull('national_code')->get()->keyBy('national_code');

        foreach (require database_path('data/student-registry.php') as $nationalCode => $students) {
            $program = $programs->get($nationalCode);
            if (! $program) {
                throw new \RuntimeException('Program studi '.$nationalCode.' belum tersedia.');
            }

            foreach ($students as [$nim, $name, $diplomaNumber]) {
                StudentRegistry::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'study_program_id' => $program->id,
                        'name' => $name,
                        'cohort' => 2023,
                        'graduation_year' => 2026,
                        'diploma_number' => $diplomaNumber,
                    ],
                );
            }
        }
    }
}
