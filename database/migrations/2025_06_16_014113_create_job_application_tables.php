<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * STRATEGY: INTERNAL SYSTEM ONLY
     * - No public registration
     * - Admin/HR creates all users
     * - No email verification needed
     * - Focus on core HR functionality
     */
    public function up(): void
    {
        // =====================================================================
        // 1. POSITIONS TABLE - SIMPLIFIED
        // =====================================================================
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name');
            $table->string('department');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->decimal('salary_range_min', 12, 2)->nullable();
            $table->decimal('salary_range_max', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);        // Simple active/inactive
            $table->string('location')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'internship'])->default('full-time');
            $table->date('posted_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['position_name', 'is_active']);
            $table->index('department');
        });

        // =====================================================================
        // 2. USERS TABLE - INTERNAL ONLY (SIMPLIFIED)
        // =====================================================================
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            // ❌ REMOVED: email_verified_at (not needed for internal system)
            $table->string('password');
            $table->string('full_name');
            $table->enum('role', ['admin', 'hr', 'interviewer'])->default('hr');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['email', 'is_active']);
            $table->index(['role', 'is_active']);
            
            // ✅ COMMENT: Internal users only - admin/HR creates all accounts
        });

        // =====================================================================
        // 3. CANDIDATES TABLE - SIMPLIFIED with Position Protection
        // =====================================================================
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_code')->unique();
            
            // Position relationship with protection
            $table->foreignId('position_id')
                  ->constrained('positions')->onDelete('restrict');
            $table->string('position_name_snapshot')->nullable(); // Simple backup
            
            $table->string('position_applied');
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('application_status', [
                'draft', 'submitted', 'screening', 'interview', 
                'offered', 'accepted', 'rejected'
            ])->default('submitted');
            $table->date('application_date')->nullable();
            
            // Personal Data (merged for performance)
            $table->string('nik', 16)->unique();
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
            $table->integer('height_cm')->unsigned()->nullable();
            $table->integer('weight_kg')->unsigned()->nullable();
            $table->enum('vaccination_status', ['Vaksin 1', 'Vaksin 2', 'Vaksin 3', 'Booster'])->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_code', 'application_status']);
            $table->index(['email', 'nik']);
            $table->index('position_id');
        });

        // =====================================================================
        // 4. FAMILY MEMBERS
        // =====================================================================
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->enum('relationship', ['Pasangan', 'Anak', 'Ayah', 'Ibu', 'Saudara'])->nullable();
            $table->string('name')->nullable();
            $table->integer('age')->unsigned()->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // =====================================================================
        // 5. FORMAL EDUCATION
        // =====================================================================
        Schema::create('formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->enum('education_level', ['SMA/SMK', 'Diploma', 'S1', 'S2', 'S3']);
            $table->string('institution_name');
            $table->string('major');
            $table->year('start_year');
            $table->year('end_year');
            $table->decimal('gpa', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'education_level']);
            $table->index('education_level');
        });

        // =====================================================================
        // 6. NON FORMAL EDUCATION
        // =====================================================================
        Schema::create('non_formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->string('course_name');
            $table->string('organizer');
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // =====================================================================
        // 7. LANGUAGE SKILLS
        // =====================================================================
        Schema::create('language_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->string('language')->nullable();
            $table->enum('speaking_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->enum('writing_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // =====================================================================
        // 8. CANDIDATE ADDITIONAL INFO
        // =====================================================================
        Schema::create('candidate_additional_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()
                  ->constrained('candidates')->onDelete('cascade');
            
            // Skills
            $table->text('hardware_skills')->nullable();
            $table->text('software_skills')->nullable();
            $table->text('other_skills')->nullable();
            
            // General Information
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
            $table->integer('absence_days')->unsigned()->nullable();
            $table->date('start_work_date')->nullable();
            $table->string('information_source')->nullable();
            $table->boolean('agreement')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_id');
        });

        // =====================================================================
        // 9. DRIVING LICENSES
        // =====================================================================
        Schema::create('driving_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->enum('license_type', ['A', 'B1', 'B2', 'C'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['candidate_id', 'license_type']);
            $table->index('candidate_id');
        });

        // =====================================================================
        // 10. WORK EXPERIENCES
        // =====================================================================
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
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

        // =====================================================================
        // 11. ACTIVITIES
        // =====================================================================
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->enum('activity_type', ['achievement', 'social_activity']);
            $table->string('title')->nullable();
            $table->string('field_or_year')->nullable();
            $table->string('period')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'activity_type']);
        });

        // =====================================================================
        // 12. DOCUMENT UPLOADS
        // =====================================================================
        Schema::create('document_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->enum('document_type', ['cv', 'photo', 'certificates', 'transcript']);
            $table->string('document_name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->integer('file_size')->unsigned()->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'document_type']);
        });

        // =====================================================================
        // 13. APPLICATION LOGS
        // =====================================================================
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->enum('action_type', ['status_change', 'document_upload', 'data_update']);
            $table->text('action_description');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'action_type']);
        });

        // =====================================================================
        // 14. INTERVIEWS
        // =====================================================================
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')
                  ->constrained('candidates')->onDelete('cascade');
            $table->date('interview_date');
            $table->time('interview_time');
            $table->string('location')->nullable();
            $table->foreignId('interviewer_id')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'status']);
        });

        // =====================================================================
        // 15. EMAIL TEMPLATES
        // =====================================================================
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

        // =====================================================================
        // 16. MINIMAL LARAVEL TABLES - INTERNAL SYSTEM ONLY
        // =====================================================================
        
        // Sessions table - simplified for internal use
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Password Reset Tokens - keep for admin/HR password reset
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ❌ REMOVED: personal_access_tokens (no API needed for internal system)
        // ❌ REMOVED: failed_jobs (no queue processing needed initially)
        // ❌ REMOVED: job_batches (no batch processing needed initially)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('application_logs');
        Schema::dropIfExists('document_uploads');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('driving_licenses');
        Schema::dropIfExists('candidate_additional_info');
        Schema::dropIfExists('language_skills');
        Schema::dropIfExists('non_formal_education');
        Schema::dropIfExists('formal_education');
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('users');
        Schema::dropIfExists('positions');
    }
};