<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. DISC 3D Sections Table - 24 sections
        Schema::create('disc_3d_sections', function (Blueprint $table) {
            $table->id();
            $table->integer('section_number')->unsigned(); // 1-24
            $table->string('section_code', 10)->unique(); // SEC01-SEC24
            $table->string('section_title')->nullable(); // Optional section title
            $table->boolean('is_active')->default(true);
            $table->integer('order_number')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['section_number', 'is_active'], 'idx_disc3d_sections_num_active');
            $table->index('order_number', 'idx_disc3d_sections_order');
        });

        // 2. DISC 3D Section Choices - 4 choices per section (96 total)
        Schema::create('disc_3d_section_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('disc_3d_sections')->onDelete('cascade');
            $table->string('section_code', 10);
            $table->integer('section_number')->unsigned();
            $table->enum('choice_dimension', ['D', 'I', 'S', 'C']);
            $table->string('choice_code', 20)->unique();
            $table->text('choice_text');
            $table->text('choice_text_en')->nullable();
            $table->decimal('weight_d', 6, 4)->default(0);
            $table->decimal('weight_i', 6, 4)->default(0);
            $table->decimal('weight_s', 6, 4)->default(0);
            $table->decimal('weight_c', 6, 4)->default(0);
            $table->enum('primary_dimension', ['D', 'I', 'S', 'C'])->nullable();
            $table->decimal('primary_strength', 5, 4)->nullable();
            $table->json('keywords')->nullable();
            $table->json('keywords_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['section_id', 'choice_dimension'], 'unq_disc3d_choices_section_dim');
            $table->index(['section_code', 'choice_dimension'], 'idx_disc3d_choices_code_dim');
            $table->index('choice_code', 'idx_disc3d_choices_code');
            $table->index('primary_dimension', 'idx_disc3d_choices_primary');
        });

        // 3. DISC 3D Test Sessions - SIMPLIFIED
        Schema::create('disc_3d_test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('test_code')->unique(); // Unique test identifier
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_duration_seconds')->nullable(); // Total time spent in test
            $table->timestamps();
            
            // Essential indexes only
            $table->index(['candidate_id', 'status']);
            $table->index('test_code');
        });

        // 4. DISC 3D Responses
        Schema::create('disc_3d_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained('disc_3d_test_sessions')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('disc_3d_sections');
            $table->string('section_code', 10);
            $table->integer('section_number')->unsigned();
            $table->foreignId('most_choice_id')->constrained('disc_3d_section_choices');
            $table->foreignId('least_choice_id')->constrained('disc_3d_section_choices');
            $table->enum('most_choice', ['D', 'I', 'S', 'C']);
            $table->enum('least_choice', ['D', 'I', 'S', 'C']);
            $table->decimal('most_score_d', 6, 4)->default(0);
            $table->decimal('most_score_i', 6, 4)->default(0);
            $table->decimal('most_score_s', 6, 4)->default(0);
            $table->decimal('most_score_c', 6, 4)->default(0);
            $table->decimal('least_score_d', 6, 4)->default(0);
            $table->decimal('least_score_i', 6, 4)->default(0);
            $table->decimal('least_score_s', 6, 4)->default(0);
            $table->decimal('least_score_c', 6, 4)->default(0);
            $table->decimal('net_score_d', 6, 4)->default(0);
            $table->decimal('net_score_i', 6, 4)->default(0);
            $table->decimal('net_score_s', 6, 4)->default(0);
            $table->decimal('net_score_c', 6, 4)->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('response_order')->unsigned();
            $table->timestamp('answered_at')->nullable();
            $table->integer('revision_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['test_session_id', 'section_id'], 'unq_disc3d_responses_session_section');
            $table->index('test_session_id', 'idx_disc3d_responses_session');
            $table->index('candidate_id', 'idx_disc3d_responses_candidate');
            $table->index(['most_choice', 'least_choice'], 'idx_disc3d_responses_choices');
            $table->index('answered_at', 'idx_disc3d_responses_answered');
            $table->index('section_number', 'idx_disc3d_responses_section_num');
        });

        // Add check constraint using raw SQL after table creation
        DB::statement('ALTER TABLE disc_3d_responses ADD CONSTRAINT check_different_choices CHECK (most_choice != least_choice)');

        // 5. DISC 3D Results - ENHANCED WITH SIMPLIFIED ACCESSORS
        Schema::create('disc_3d_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->unique()->constrained('disc_3d_test_sessions')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('test_code', 50);
            $table->timestamp('test_completed_at');
            $table->integer('test_duration_seconds');
            
            // GRAPH 1 - MOST (Mask Public Self) Raw Scores
            $table->decimal('most_d_raw', 8, 4)->default(0);
            $table->decimal('most_i_raw', 8, 4)->default(0);
            $table->decimal('most_s_raw', 8, 4)->default(0);
            $table->decimal('most_c_raw', 8, 4)->default(0);
            
            // MOST Percentages (0-100)
            $table->decimal('most_d_percentage', 5, 2)->default(0);
            $table->decimal('most_i_percentage', 5, 2)->default(0);
            $table->decimal('most_s_percentage', 5, 2)->default(0);
            $table->decimal('most_c_percentage', 5, 2)->default(0);
            
            // MOST Segments (1-7 scale)
            $table->integer('most_d_segment')->unsigned()->nullable();
            $table->integer('most_i_segment')->unsigned()->nullable();
            $table->integer('most_s_segment')->unsigned()->nullable();
            $table->integer('most_c_segment')->unsigned()->nullable();
            
            // GRAPH 2 - LEAST (Core Private Self) Raw Scores
            $table->decimal('least_d_raw', 8, 4)->default(0);
            $table->decimal('least_i_raw', 8, 4)->default(0);
            $table->decimal('least_s_raw', 8, 4)->default(0);
            $table->decimal('least_c_raw', 8, 4)->default(0);
            
            // LEAST Percentages (0-100)
            $table->decimal('least_d_percentage', 5, 2)->default(0);
            $table->decimal('least_i_percentage', 5, 2)->default(0);
            $table->decimal('least_s_percentage', 5, 2)->default(0);
            $table->decimal('least_c_percentage', 5, 2)->default(0);
            
            // LEAST Segments (1-7 scale)
            $table->integer('least_d_segment')->unsigned()->nullable();
            $table->integer('least_i_segment')->unsigned()->nullable();
            $table->integer('least_s_segment')->unsigned()->nullable();
            $table->integer('least_c_segment')->unsigned()->nullable();
            
            // GRAPH 3 - CHANGE (Mirror Perceived Self) - Difference scores
            $table->decimal('change_d_raw', 8, 4)->default(0);
            $table->decimal('change_i_raw', 8, 4)->default(0);
            $table->decimal('change_s_raw', 8, 4)->default(0);
            $table->decimal('change_c_raw', 8, 4)->default(0);
            
            // CHANGE Segments (can be negative)
            $table->integer('change_d_segment')->nullable();
            $table->integer('change_i_segment')->nullable();
            $table->integer('change_s_segment')->nullable();
            $table->integer('change_c_segment')->nullable();
            
            // Primary and Secondary patterns for each graph
            $table->enum('most_primary_type', ['D', 'I', 'S', 'C'])->nullable();
            $table->enum('most_secondary_type', ['D', 'I', 'S', 'C'])->nullable();
            $table->enum('least_primary_type', ['D', 'I', 'S', 'C'])->nullable();
            $table->enum('least_secondary_type', ['D', 'I', 'S', 'C'])->nullable();
            
            // Pattern combinations
            $table->string('most_pattern', 10)->nullable(); // e.g., "DI", "DC"
            $table->string('least_pattern', 10)->nullable();
            $table->string('adaptation_pattern', 20)->nullable(); // e.g., "DI_to_SC"
            
            // SIMPLIFIED ACCESSORS FOR CANDIDATE MODEL INTEGRATION
            $table->enum('primary_type', ['D', 'I', 'S', 'C'])->nullable(); // Main personality type
            $table->enum('secondary_type', ['D', 'I', 'S', 'C'])->nullable(); // Secondary type
            $table->string('personality_profile', 100)->nullable(); // e.g., "Decisive Leader (DI)"
            $table->decimal('primary_percentage', 5, 2)->nullable(); // Strength of primary type
            $table->text('summary')->nullable(); // Brief personality summary for dashboard
            
            // JSON data for complete analysis
            $table->json('graph_most_data')->nullable();
            $table->json('graph_least_data')->nullable();
            $table->json('graph_change_data')->nullable();
            
            // Score breakdowns
            $table->json('most_score_breakdown')->nullable(); // Section-by-section MOST scores
            $table->json('least_score_breakdown')->nullable(); // Section-by-section LEAST scores
            
            // Profile interpretations
            $table->text('public_self_summary')->nullable();
            $table->text('private_self_summary')->nullable();
            $table->text('adaptation_summary')->nullable();
            $table->text('overall_profile')->nullable();
            
            // Detailed analysis
            $table->json('section_responses')->nullable();
            $table->json('stress_indicators')->nullable();
            $table->json('behavioral_insights')->nullable();
            $table->json('consistency_analysis')->nullable();
            
            // Validity indicators
            $table->decimal('consistency_score', 5, 2)->nullable(); // 0-100
            $table->boolean('is_valid')->default(true);
            $table->json('validity_flags')->nullable();
            
            // Performance metrics
            $table->decimal('response_consistency', 5, 2)->nullable(); // Response pattern consistency
            $table->integer('average_response_time')->nullable(); // Average time per section
            $table->json('timing_analysis')->nullable();

            // Work style interpretations
            $table->json('work_style_most')->nullable();
            $table->json('work_style_least')->nullable();
            $table->json('work_style_adaptation')->nullable();

            // Communication style interpretations  
            $table->json('communication_style_most')->nullable();
            $table->json('communication_style_least')->nullable();

            // Stress behavior patterns
            $table->json('stress_behavior_most')->nullable();
            $table->json('stress_behavior_least')->nullable();
            $table->json('stress_behavior_change')->nullable();

            // Motivators and fears
            $table->json('motivators_most')->nullable();
            $table->json('motivators_least')->nullable();
            $table->json('fears_most')->nullable();
            $table->json('fears_least')->nullable();

            // Compiled interpretations for easy access
            $table->text('work_style_summary')->nullable();
            $table->text('communication_summary')->nullable();
            $table->text('motivators_summary')->nullable();
            $table->text('stress_management_summary')->nullable();

            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['candidate_id', 'most_primary_type', 'least_primary_type'], 'idx_disc3d_results_candidate_types');
            $table->index(['candidate_id', 'primary_type'], 'idx_disc3d_results_candidate_primary');
            $table->index(['most_pattern', 'least_pattern'], 'idx_disc3d_results_patterns');
            $table->index('test_completed_at', 'idx_disc3d_results_completed');
            $table->index('created_at', 'idx_disc3d_results_created');
            $table->index('primary_type', 'idx_disc3d_results_primary_type');
            $table->index('is_valid', 'idx_disc3d_results_valid');
        });

        // 6. DISC 3D Profile Interpretations
        Schema::create('disc_3d_profile_interpretations', function (Blueprint $table) {
            $table->id();
            $table->enum('dimension', ['D', 'I', 'S', 'C']);
            $table->enum('graph_type', ['MOST', 'LEAST', 'CHANGE']);
            $table->integer('segment_level');
            $table->string('title', 100)->nullable();
            $table->string('title_en', 100)->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            $table->json('characteristics')->nullable();
            $table->json('characteristics_en')->nullable();
            $table->json('behavioral_indicators')->nullable();
            $table->json('work_style')->nullable();
            $table->json('communication_style')->nullable();
            $table->json('stress_behavior')->nullable();
            $table->json('motivators')->nullable();
            $table->json('fears')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['dimension', 'graph_type', 'segment_level'], 'unq_disc3d_interpretations_dim_graph_seg');
            $table->index(['graph_type', 'dimension'], 'idx_disc3d_interpretations_graph_dim');
            $table->index('segment_level', 'idx_disc3d_interpretations_segment');
            $table->index(['dimension', 'graph_type', 'segment_level'], 'idx_disc3d_interpretations_lookup');
        });

        // 7. DISC 3D Pattern Combinations
        Schema::create('disc_3d_pattern_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_code', 10)->unique();
            $table->string('pattern_name', 100);
            $table->string('pattern_name_en', 100)->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('ideal_environment')->nullable();
            $table->json('communication_tips')->nullable();
            $table->json('career_matches')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pattern_code', 'idx_disc3d_patterns_code');
        });

        // 8. DISC 3D Configuration - Simplified for scoring only
        Schema::create('disc_3d_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 100)->unique();
            $table->text('config_value');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('config_key', 'idx_disc3d_config_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint first
        DB::statement('ALTER TABLE disc_3d_responses DROP CONSTRAINT IF EXISTS check_different_choices');
        
        Schema::dropIfExists('disc_3d_config');
        Schema::dropIfExists('disc_3d_pattern_combinations');
        Schema::dropIfExists('disc_3d_profile_interpretations');
        Schema::dropIfExists('disc_3d_results');
        Schema::dropIfExists('disc_3d_responses');
        Schema::dropIfExists('disc_3d_test_sessions');
        Schema::dropIfExists('disc_3d_section_choices');
        Schema::dropIfExists('disc_3d_sections');
    }
};