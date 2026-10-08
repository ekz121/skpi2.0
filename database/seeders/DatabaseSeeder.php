<?php

namespace Database\Seeders;

use App\Models\ActivityRule;
use App\Models\StudentProfile;
use App\Models\StudyProgram;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $programs = collect([
            ['code' => 'TI', 'name' => 'Teknologi Informasi'],
            ['code' => 'TM', 'name' => 'Teknologi Mesin'],
            ['code' => 'AP', 'name' => 'Administrasi Perkantoran'],
            ['code' => 'AK', 'name' => 'Akuntansi'],
        ])->mapWithKeys(function (array $program) {
            $outcomes = $program['code'] === 'TI' ? [
                'Sikap dan Tata Nilai' => [
                    'Bertakwa kepada Tuhan Yang Maha Esa dan menunjukkan sikap religius.',
                    'Menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan agama, moral, dan etika.',
                    'Menginternalisasi nilai, norma, dan etika akademik.',
                ],
                'Kemampuan Kerja Umum' => [
                    'Mampu menyelesaikan pekerjaan berlingkup luas dan memilih metode yang sesuai dengan menganalisis data.',
                    'Mampu menunjukkan kinerja bermutu dan terukur.',
                    'Mampu memecahkan masalah pekerjaan sesuai bidang keahlian terapannya.',
                ],
                'Kemampuan Kerja Khusus' => [
                    'Mampu merancang, membangun, menguji, dan mengelola solusi berbasis perangkat lunak dan jaringan komputer secara bertanggung jawab.',
                    'Mampu menerapkan algoritma, basis data, rekayasa perangkat lunak, dan infrastruktur TI untuk menyelesaikan masalah.',
                ],
                'Penguasaan Pengetahuan' => [
                    'Menguasai konsep teoritis Teknologi Informasi, meliputi algoritma, struktur data, basis data, dan jaringan komputer.',
                    'Menguasai prinsip metode penyelesaian masalah teknis dalam pengembangan perangkat lunak.',
                ],
            ] : [];

            $model = StudyProgram::create($program + ['learning_outcomes' => $outcomes]);

            return [$program['code'] => $model];
        });

        $rules = [];
        foreach (['Prestasi lomba akademik', 'Prestasi lomba nonakademik'] as $subcategory) {
            $achievementRows = [
                ['Nasional', 'Juara 1', 200], ['Nasional', 'Juara 2', 180], ['Nasional', 'Juara 3', 150], ['Nasional', 'Juara Harapan', 130], ['Nasional', 'Keikutsertaan', 30],
                ['Provinsi/Regional', 'Juara 1', 150], ['Provinsi/Regional', 'Juara 2', 130], ['Provinsi/Regional', 'Juara 3', 100], ['Provinsi/Regional', 'Juara Harapan', 80], ['Provinsi/Regional', 'Keikutsertaan', 30],
                ['Kota/Kabupaten', 'Juara 1', 80], ['Kota/Kabupaten', 'Juara 2', 60], ['Kota/Kabupaten', 'Juara 3', 50], ['Kota/Kabupaten', 'Juara Harapan', 40], ['Kota/Kabupaten', 'Keikutsertaan', 20],
                ['DIKTI', 'Juara 1', 150], ['DIKTI', 'Juara 2', 130], ['DIKTI', 'Juara 3', 100], ['DIKTI', 'Juara Harapan', 80], ['DIKTI', 'Keikutsertaan', 30],
            ];
            foreach ($achievementRows as [$level, $achievement, $points]) {
                $rules[] = [
                    'category' => 'Prestasi Akademik dan Nonakademik', 'subcategory' => $subcategory,
                    'activity_type' => 'Lomba', 'level' => $level, 'achievement' => $achievement,
                    'points' => $points, 'evidence_label' => $achievement === 'Keikutsertaan' ? 'Sertifikat Keikutsertaan' : 'Sertifikat '.$achievement,
                ];
            }
        }

        $trainingRows = [
            ['Sertifikasi/pelatihan Disnaker', 150, 'Sertifikat Kompetensi', false],
            ['Sertifikasi/pelatihan BNSP', 100, 'Sertifikat Kompetensi', true],
            ['Sertifikat Kompetensi dari LSP dalam dan luar negeri', 150, 'Sertifikat Kompetensi', false],
            ['Magang/PKL 3-6 bulan (MSIB/Magenta)', 80, 'Sertifikat Keikutsertaan', false],
            ['Magang/PKL 3-6 bulan (reguler)', 60, 'Sertifikat Keikutsertaan', true],
            ['PKKMB', 40, 'Sertifikat Keikutsertaan', true],
            ['Pelatihan 3 hari atau lebih', 80, 'Sertifikat Keikutsertaan', false],
            ['Pelantikan LKMM Pra TD/TD', 40, 'Sertifikat Keikutsertaan', true],
            ['Seminar nasional sehari', 30, 'Sertifikat Keikutsertaan', false],
            ['Pelatihan/seminar sehari dari perusahaan', 20, 'Sertifikat Keikutsertaan', false],
            ['Seminar online/webinar sehari', 10, 'Sertifikat Keikutsertaan', false],
        ];
        foreach ($trainingRows as [$type, $points, $evidence, $mandatory]) {
            $rules[] = [
                'category' => 'Pelatihan dan Keprofesian', 'subcategory' => 'Pelatihan, sertifikasi, magang, dan seminar',
                'activity_type' => $type, 'points' => $points, 'evidence_label' => $evidence, 'is_mandatory' => $mandatory,
            ];
        }

        $organizationRows = [
            ['Presiden BEM', 100], ['Wakil Presiden BEM', 100], ['Ketua DPM', 100],
            ['Ketua organisasi luar kampus tingkat nasional', 100], ['Wakil ketua luar kampus tingkat nasional', 100],
            ['Ketua HIMA', 80], ['Wakil Ketua HIMA', 80], ['Ketua organisasi luar kampus tingkat regional/provinsi', 80],
            ['Wakil ketua luar kampus tingkat regional/provinsi', 80], ['Koordinator/Menteri/Sekretaris/Bendahara BEM', 70],
            ['Anggota BEM', 50], ['Anggota organisasi luar kampus tingkat nasional', 50], ['Anggota HIMA', 40],
            ['Ketua dan wakil kegiatan', 40], ['Ketua organisasi luar kampus tingkat kabupaten/kota', 40],
            ['Wakil ketua luar kampus tingkat kabupaten/kota', 40], ['Anggota organisasi luar kampus tingkat regional/provinsi', 40],
            ['Koordinator kegiatan', 40], ['Anggota organisasi luar kampus tingkat kabupaten/kota', 30],
            ['Keikutsertaan ekstrakurikuler minimal 1 semester dan hadir minimal 10 kali', 50], ['Anggota/volunteer kegiatan', 30],
        ];
        foreach ($organizationRows as [$type, $points]) {
            $rules[] = [
                'category' => 'Organisasi dan Kepemimpinan', 'subcategory' => 'Jabatan, kepanitiaan, dan ekstrakurikuler',
                'activity_type' => $type, 'points' => $points,
                'evidence_label' => str_contains($type, 'Keikutsertaan') ? 'Sertifikat Keikutsertaan Ekstrakurikuler' : 'Sertifikat atau SK resmi',
            ];
        }

        $rules[] = [
            'category' => 'Proyek, Penelitian, dan Pengabdian Masyarakat', 'subcategory' => 'Proyek, penelitian, dan pengabdian',
            'activity_type' => 'Keterlibatan lebih dari 3 bulan', 'duration_label' => '> 3 bulan', 'points' => 50,
            'evidence_label' => 'Bukti jurnal/proyek keikutsertaan',
        ];
        $rules[] = [
            'category' => 'Proyek, Penelitian, dan Pengabdian Masyarakat', 'subcategory' => 'Proyek, penelitian, dan pengabdian',
            'activity_type' => 'Keterlibatan 2 minggu sampai 1 bulan', 'duration_label' => '2 minggu - 1 bulan', 'points' => 30,
            'evidence_label' => 'Bukti jurnal/proyek keikutsertaan',
        ];

        foreach ($rules as $rule) {
            ActivityRule::create($rule + ['is_mandatory' => $rule['is_mandatory'] ?? false, 'is_active' => true]);
        }

        User::create([
            'name' => 'Admin SKPI', 'email' => 'admin@polteksi.ac.id', 'role' => 'admin',
            'email_verified_at' => now(), 'password' => Hash::make('polteksi123'),
        ]);
        $student = User::create([
            'name' => 'Mahasiswa Demo', 'email' => 'mahasiswa@demo.polteksi.ac.id', 'role' => 'student',
            'email_verified_at' => now(), 'password' => Hash::make('demo12345'),
        ]);
        StudentProfile::create([
            'user_id' => $student->id, 'study_program_id' => $programs['TI']->id,
            'nim' => 'DEMO-2023-001', 'cohort' => 2023, 'birthplace' => 'Gresik', 'birthdate' => '2004-04-18',
            'graduation_year' => 2026, 'diploma_number' => 'DEMO-IJAZAH-001', 'academic_title' => 'A.Md.Kom.',
        ]);

        $seedActivities = [
            ['Prestasi lomba akademik', 'Nasional', 'Juara 1', 'Juara 1 Lomba Inovasi Teknologi', 'Forum Pendidikan Nasional', '2024-05-20'],
            [null, null, null, 'Sertifikasi Kompetensi Disnaker', 'Dinas Tenaga Kerja', '2024-08-12', 'Sertifikasi/pelatihan Disnaker'],
            [null, null, null, 'Ketua Himpunan Mahasiswa', 'HIMA Teknologi Informasi', '2025-01-10', 'Ketua HIMA'],
            [null, null, null, 'Proyek Digitalisasi Arsip Kampus', 'Politeknik Semen Indonesia', '2025-02-01', 'Keterlibatan lebih dari 3 bulan'],
            [null, null, null, 'Magang dan Studi Independen Bersertifikat', 'Mitra MSIB', '2025-07-01', 'Magang/PKL 3-6 bulan (MSIB/Magenta)'],
        ];
        foreach ($seedActivities as $index => $item) {
            $rule = isset($item[6])
                ? ActivityRule::where('activity_type', $item[6])->firstOrFail()
                : ActivityRule::where('subcategory', $item[0])->where('level', $item[1])->where('achievement', $item[2])->firstOrFail();
            Submission::create([
                'user_id' => $student->id, 'activity_rule_id' => $rule->id, 'activity_name' => $item[3],
                'organizer' => $item[4], 'started_at' => $item[5], 'status' => 'approved',
                'estimated_points' => $rule->points, 'approved_points' => $rule->points,
                'submitted_at' => now()->subDays(20 - $index), 'verified_at' => now()->subDays(15 - $index),
            ]);
        }

        $pendingRule = ActivityRule::where('activity_type', 'Seminar nasional sehari')->firstOrFail();
        Submission::create([
            'user_id' => $student->id, 'activity_rule_id' => $pendingRule->id,
            'activity_name' => 'Seminar Nasional Transformasi Industri', 'organizer' => 'Forum Teknologi Terapan',
            'started_at' => now()->subDays(3), 'status' => 'approved', 'estimated_points' => $pendingRule->points,
            'approved_points' => $pendingRule->points, 'submitted_at' => now()->subDays(2), 'verified_at' => now()->subDays(2),
        ]);
    }
}
