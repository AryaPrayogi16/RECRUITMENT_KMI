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
        // 1. Kraeplin Questions Table (Static Questions - TIDAK BERUBAH)
        Schema::create('kraeplin_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('column_number')->unsigned(); // 1-32
            $table->integer('row_number')->unsigned(); // 1-27  
            $table->integer('value')->unsigned(); // Single digit (0-9)
            $table->timestamps();
            
            // Composite unique index to prevent duplicates
            $table->unique(['column_number', 'row_number']);
            $table->index('column_number');
        });

        // 2. Kraeplin Test Sessions (SIMPLIFIED)
        Schema::create('kraeplin_test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('test_code')->unique(); // Unique test identifier
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_duration_seconds')->nullable(); // Total time spent in test
            $table->timestamps();
            
            $table->index(['candidate_id', 'status']);
            $table->index('test_code');
        });

        // 3. Kraeplin Answers (SIMPLIFIED - Bulk Insert)
        Schema::create('kraeplin_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained('kraeplin_test_sessions')->onDelete('cascade');
            $table->integer('column_number')->unsigned(); // 1-32
            $table->integer('row_number')->unsigned(); // 1-26 (ada 26 pertanyaan per kolom)
            $table->integer('question_value_1')->unsigned(); // First number
            $table->integer('question_value_2')->unsigned(); // Second number  
            $table->integer('correct_answer')->unsigned(); // Expected answer (last digit)
            $table->integer('user_answer')->nullable(); // User's answer (0-9)
            $table->boolean('is_correct')->default(false);
            $table->integer('time_spent_seconds')->default(0); // Time spent on this column (0-15)
            $table->timestamps();
            
            $table->index(['test_session_id', 'column_number']);
            $table->index(['test_session_id', 'is_correct']);
        });

        // 4. Kraeplin Test Results (UPDATED WITH CHART DATA)
        Schema::create('kraeplin_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->unique()->constrained('kraeplin_test_sessions')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            
            // Basic Scores
            $table->integer('total_questions_answered')->default(0);
            $table->integer('total_correct_answers')->default(0);
            $table->integer('total_wrong_answers')->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->default(0); // Correct/Answered * 100
            
            // Performance Metrics
            $table->decimal('average_speed_per_column', 8, 2)->default(0); // Avg questions per column
            $table->decimal('overall_score', 5, 2)->default(0); // Final score (0-100)
            
            // Performance Category
            $table->enum('performance_category', [
                'excellent', 'good', 'average', 'below_average', 'poor'
            ])->nullable();
            
            // Column Performance Data (JSON - 32 values for charts)
            $table->json('column_accuracy')->nullable(); // Accuracy per column [0-100, 0-100, ...]
            $table->json('column_speed')->nullable(); // Questions answered per column [0-26, 0-26, ...]
            
            // ========== NEW FIELDS FOR ENHANCED CHART DATA ==========
            $table->json('column_correct_count')->nullable(); // Jumlah jawaban benar per kolom [0-26, 0-26, ...]
            $table->json('column_answered_count')->nullable(); // Jumlah soal dijawab per kolom [0-26, 0-26, ...]
            $table->json('column_avg_time')->nullable(); // Rata-rata waktu per soal per kolom [0.5, 1.2, ...]
            // ========================================================
            
            $table->timestamps();
            
            $table->index(['candidate_id', 'overall_score']);
        });

        // Insert static questions from Excel data
        $this->insertKraeplinQuestions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kraeplin_test_results');
        Schema::dropIfExists('kraeplin_answers');
        Schema::dropIfExists('kraeplin_test_sessions');
        Schema::dropIfExists('kraeplin_questions');
    }

    /**
     * Insert the static Kraeplin questions from Excel data
     * TIDAK BERUBAH - Soal tetap sama seperti original
     */
    private function insertKraeplinQuestions(): void
    {
        // Data from Excel - 32 columns x 27 rows (TIDAK DIUBAH)
        $kraeplinData = [
            // Column 1
            1 => [2,7,7,9,7,4,6,6,9,2,2,1,1,8,2,2,6,5,9,2,9,8,6,4,7,2,9,4],
            2 => [7,5,6,9,6,7,1,9,5,3,6,3,5,2,9,5,6,4,2,6,8,5,7,1,6,1,8,3],
            3 => [1,5,1,9,3,2,3,6,9,8,9,1,1,2,4,8,5,7,5,9,1,8,6,5,7,5,5,5],
            4 => [2,3,4,2,5,6,7,7,3,2,6,7,8,8,4,7,8,7,5,3,5,7,8,8,2,4,6,5],
            5 => [3,6,1,2,7,9,2,8,7,4,3,3,7,6,2,9,3,9,5,7,3,3,9,9,6,1,3,1],
            6 => [6,8,2,9,6,7,1,8,9,6,4,1,7,9,7,8,8,6,6,5,2,5,8,7,3,7,1,7],
            7 => [5,4,3,8,4,2,2,7,4,9,5,2,7,2,4,2,3,5,6,9,4,6,5,6,6,1,7,5],
            8 => [5,5,8,8,1,5,1,5,4,9,7,8,8,6,6,2,6,3,3,5,2,4,4,7,7,9,1,1],
            9 => [7,7,5,6,2,5,2,3,4,1,1,9,7,6,2,8,5,7,7,1,9,6,1,3,4,4,1,6],
            10 => [9,1,9,9,4,6,5,4,5,2,3,5,7,2,3,6,3,1,4,9,6,8,7,4,1,9,3,5],
            11 => [9,9,7,3,8,4,3,4,5,9,3,4,5,1,7,4,8,8,5,8,4,9,8,3,5,5,5,4],
            12 => [4,9,6,7,3,9,1,2,5,5,8,7,2,3,3,9,9,7,7,2,3,6,2,6,6,8,9,7],
            13 => [4,2,3,6,8,3,5,9,8,6,8,3,6,3,2,6,4,5,3,7,5,2,4,9,8,7,1,3],
            14 => [3,3,2,9,9,8,5,9,5,5,2,9,6,4,1,1,9,9,3,3,8,4,2,2,7,2,5,3],
            15 => [4,7,9,5,3,8,9,3,5,4,5,9,5,8,8,8,9,4,1,2,6,8,4,5,5,5,2,1],
            16 => [5,9,1,8,3,9,9,3,6,6,2,5,9,3,7,8,4,1,8,4,6,7,3,6,2,4,7,2],
            17 => [7,4,6,8,2,3,5,5,6,7,6,7,7,6,5,4,9,7,2,9,4,9,1,7,3,1,8,2],
            18 => [8,6,1,3,9,3,8,3,3,7,5,7,5,3,3,2,4,5,6,9,5,4,7,1,3,8,4,3],
            19 => [2,1,1,7,2,4,9,7,1,9,1,9,4,8,2,5,3,6,6,4,9,2,4,6,7,1,7,6],
            20 => [6,8,4,7,1,8,8,7,5,4,7,7,8,5,1,4,6,1,9,5,5,5,4,3,5,9,7,2],
            21 => [4,2,2,6,6,2,3,2,8,8,1,8,5,9,8,9,3,2,7,7,1,4,6,7,3,8,1,6],
            22 => [9,1,2,1,6,5,7,4,7,1,9,6,3,9,7,2,4,9,1,7,1,2,2,8,9,2,4,9],
            23 => [5,2,9,9,2,2,2,6,8,3,6,9,2,3,4,4,1,2,2,6,8,6,1,9,1,9,4,4],
            24 => [9,5,4,7,8,4,1,9,1,4,2,4,7,9,7,5,6,7,8,3,1,5,9,6,7,4,2,1],
            25 => [3,3,5,6,2,2,5,1,7,7,8,2,9,9,3,7,2,3,7,6,5,4,1,6,7,3,7,2],
            26 => [2,7,4,1,2,1,3,2,6,2,6,5,5,9,3,5,9,5,9,5,9,6,3,1,4,1,8,1],
            27 => [1,6,5,1,2,9,5,8,7,1,9,7,7,8,2,6,9,7,3,1,7,4,9,4,8,3,7,6],
            28 => [3,2,7,4,2,7,3,1,5,6,4,9,2,4,9,5,2,1,7,4,5,9,4,8,5,6,5,7],
            29 => [8,1,3,7,1,7,7,2,3,2,5,6,3,7,7,4,9,7,5,4,8,4,3,7,5,2,3,3],
            30 => [5,9,3,4,6,8,6,4,6,3,1,5,6,5,6,9,4,3,5,9,4,2,8,7,3,2,9,2],
            31 => [6,6,6,2,6,7,1,5,6,4,7,3,8,9,7,1,7,5,6,2,5,1,2,3,1,2,8,3],
            32 => [9,3,6,7,2,8,7,3,7,9,2,4,7,4,1,3,1,3,9,9,1,6,8,6,3,9,7,3]
        ];

        $questions = [];
        $timestamp = now();

        foreach ($kraeplinData as $column => $rows) {
            foreach ($rows as $index => $value) {
                $questions[] = [
                    'column_number' => $column,
                    'row_number' => $index + 1, // Row numbers start from 1
                    'value' => $value,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        // Insert in chunks for better performance
        $chunks = array_chunk($questions, 100);
        foreach ($chunks as $chunk) {
            DB::table('kraeplin_questions')->insert($chunk);
        }
    }
};