<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name')->unique();
            $table->string('accreditation')->nullable();
            $table->json('learning_outcomes')->nullable();
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('nim', 30)->unique();
            $table->year('cohort');
            $table->string('birthplace')->nullable();
            $table->date('birthdate')->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('diploma_number')->nullable();
            $table->string('academic_title')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_rules', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('subcategory');
            $table->string('activity_type');
            $table->string('level')->nullable();
            $table->string('achievement')->nullable();
            $table->string('duration_label')->nullable();
            $table->unsignedSmallInteger('points');
            $table->string('evidence_label');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category', 'subcategory']);
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_rule_id')->constrained()->restrictOnDelete();
            $table->string('activity_name');
            $table->string('organizer');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('verification_url')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();
            $table->enum('status', ['draft', 'pending', 'revision', 'approved', 'rejected'])->default('draft')->index();
            $table->unsignedSmallInteger('estimated_points');
            $table->unsignedSmallInteger('approved_points')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('submission_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('points')->nullable();
            $table->timestamps();
        });

        Schema::create('skpi_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'revision', 'issued', 'failed'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->string('document_number')->nullable()->unique();
            $table->string('pdf_path')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('skpi_requests');
        Schema::dropIfExists('submission_decisions');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('activity_rules');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('study_programs');
    }
};
