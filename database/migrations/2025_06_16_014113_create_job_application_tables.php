<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Positions table (must be first due to foreign key)
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name');
            $table->string('department');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->decimal('salary_range_min', 12, 2)->nullable();
            $table->decimal('salary_range_max', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('location')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'internship'])->default('full-time');
            $table->date('posted_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['position_name', 'is_active']);
        });

        // 2. Users table (for HR system)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('full_name');
            $table->enum('role', ['admin', 'hr', 'interviewer'])->default('hr');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['username', 'email', 'role']);
        });

        // 3. Candidates table (main table)
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_code')->unique();
            $table->foreignId('position_id')->constrained('positions');
            $table->string('position_applied');
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('application_status', [
                'draft', 'submitted', 'screening', 'interview', 
                'offered', 'accepted', 'rejected'
            ])->default('submitted');
            $table->date('application_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_code', 'application_status', 'position_id']);
        });

        // 4. Personal Data table
        Schema::create('personal_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained('candidates')->onDelete('cascade');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('phone_alternative')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('religion')->nullable();
            $table->enum('marital_status', ['Lajang', 'Menikah', 'Janda', 'Duda'])->nullable();
            $table->string('ethnicity')->nullable();
            $table->text('current_address')->nullable();
            $table->enum('current_address_status', ['Milik Sendiri', 'Orang Tua', 'Kontrak', 'Sewa'])->nullable();
            $table->text('ktp_address')->nullable();
            $table->integer('height_cm')->nullable();
            $table->integer('weight_kg')->nullable();
            $table->enum('vaccination_status', ['Vaksin 1', 'Vaksin 2', 'Vaksin 3', 'Booster'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['email', 'candidate_id']);
        });

        // 5. Family Members table
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->enum('relationship', ['Pasangan', 'Anak', 'Ayah', 'Ibu', 'Saudara'])->nullable();
            $table->string('name')->nullable();
            $table->integer('age')->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 6. Education tables (Formal & Non-formal)
        Schema::create('formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->enum('education_level', ['SMA/SMK', 'Diploma', 'S1', 'S2', 'S3'])->nullable();
            $table->string('institution_name')->nullable();
            $table->string('major')->nullable();
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        Schema::create('non_formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('course_name')->nullable();
            $table->string('organizer')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 7. Skills tables (Language, Computer, Other)
        Schema::create('language_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('language')->nullable();
            $table->enum('speaking_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->enum('writing_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        Schema::create('computer_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained('candidates')->onDelete('cascade');
            $table->text('hardware_skills')->nullable();
            $table->text('software_skills')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        Schema::create('other_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained('candidates')->onDelete('cascade');
            $table->text('other_skills')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 8. Driving Licenses table
        Schema::create('driving_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->enum('license_type', ['A', 'B1', 'B2', 'C'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['candidate_id', 'license_type']);
            $table->index('candidate_id');
        });

        // 9. Work Experience table
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_field')->nullable();
            $table->string('position')->nullable();
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->string('supervisor_contact')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 10. Achievements & Social Activities
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('achievement')->nullable();
            $table->year('year')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        Schema::create('social_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('organization_name')->nullable();
            $table->string('field')->nullable();
            $table->string('period')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 11. General Information table
        Schema::create('general_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained('candidates')->onDelete('cascade');
            $table->boolean('willing_to_travel')->default(false);
            $table->boolean('has_vehicle')->default(false);
            $table->string('vehicle_types')->nullable();
            $table->text('motivation')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->string('other_income')->nullable();
            $table->boolean('has_police_record')->default(false);
            $table->string('police_record_detail')->nullable();
            $table->boolean('has_serious_illness')->default(false);
            $table->string('illness_detail')->nullable();
            $table->boolean('has_tattoo_piercing')->default(false);
            $table->string('tattoo_piercing_detail')->nullable();
            $table->boolean('has_other_business')->default(false);
            $table->string('other_business_detail')->nullable();
            $table->integer('absence_days')->nullable();
            $table->date('start_work_date')->nullable();
            $table->string('information_source')->nullable();
            $table->boolean('agreement')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // 12. Document Uploads table
        Schema::create('document_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->enum('document_type', ['cv', 'photo', 'certificates', 'transcript']);
            $table->string('document_name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'document_type']);
        });

        // 13. HR System tables (Application Logs, Interviews, Email Templates)
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('action_type', ['status_change', 'document_upload', 'data_update']);
            $table->text('action_description');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'user_id', 'action_type']);
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->date('interview_date');
            $table->time('interview_time');
            $table->string('location')->nullable();
            $table->foreignId('interviewer_id')->nullable()->constrained('users');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'interviewer_id', 'status']);
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100);
            $table->string('subject', 200);
            $table->text('body');
            $table->enum('template_type', ['application_received', 'interview_invitation', 'acceptance', 'rejection']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['template_type', 'is_active']);
        });

        // 14. Sessions table (for Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('application_logs');
        Schema::dropIfExists('document_uploads');
        Schema::dropIfExists('general_information');
        Schema::dropIfExists('social_activities');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('driving_licenses');
        Schema::dropIfExists('other_skills');
        Schema::dropIfExists('computer_skills');
        Schema::dropIfExists('language_skills');
        Schema::dropIfExists('non_formal_education');
        Schema::dropIfExists('formal_education');
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('personal_data');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('users');
        Schema::dropIfExists('positions');
    }
};