<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('custom_activity_type', 180)->nullable()->after('activity_rule_id');
        });

        $now = now();
        DB::table('activity_rules')->updateOrInsert(
            [
                'category' => 'Sertifikat Magang',
                'subcategory' => 'Magang',
                'activity_type' => 'Magang Industri',
                'level' => null,
                'achievement' => null,
                'duration_label' => null,
            ],
            [
                'points' => 60,
                'evidence_label' => 'Sertifikat magang',
                'is_mandatory' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
        DB::table('activity_rules')->updateOrInsert(
            [
                'category' => 'Sertifikat Lain-lain',
                'subcategory' => 'Lain-lain',
                'activity_type' => 'Lain-lain',
                'level' => null,
                'achievement' => null,
                'duration_label' => null,
            ],
            [
                'points' => 0,
                'evidence_label' => 'Sertifikat atau dokumen resmi',
                'is_mandatory' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('custom_activity_type');
        });

        DB::table('activity_rules')
            ->whereIn('category', ['Sertifikat Magang', 'Sertifikat Lain-lain'])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('submissions')
                    ->whereColumn('submissions.activity_rule_id', 'activity_rules.id');
            })
            ->delete();
    }
};
