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
        // 1. DISC Questions Table - WITH ALL WEIGHTS
        Schema::create('disc_questions', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 10)->unique();
            $table->text('question_text_en');
            $table->text('question_text_id')->nullable();
            
            // Weights for ALL dimensions (can be positive or negative)
            $table->decimal('weight_d', 6, 4)->default(0);
            $table->decimal('weight_i', 6, 4)->default(0);
            $table->decimal('weight_s', 6, 4)->default(0);
            $table->decimal('weight_c', 6, 4)->default(0);
            
            $table->enum('primary_dimension', ['D', 'I', 'S', 'C'])->nullable();
            $table->decimal('primary_strength', 5, 4)->nullable();
            $table->integer('order_number')->unsigned();
            $table->boolean('is_core_16')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['primary_dimension', 'is_active']);
            $table->index('order_number');
            $table->index('is_core_16');
        });

        // Other tables remain the same as before...
        Schema::create('disc_test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('test_code')->unique();
            $table->enum('test_type', ['core_16', 'full_50'])->default('core_16');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_duration_seconds')->nullable();
            $table->enum('language', ['en', 'id'])->default('id');
            $table->timestamps();
            
            $table->index(['candidate_id', 'status']);
            $table->index(['test_code', 'test_type']);
        });

        Schema::create('disc_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained('disc_test_sessions')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('disc_questions');
            $table->string('item_code', 10);
            $table->integer('response')->unsigned();
            
            // Weighted scores for EACH dimension
            $table->decimal('weighted_score_d', 6, 4)->default(0);
            $table->decimal('weighted_score_i', 6, 4)->default(0);
            $table->decimal('weighted_score_s', 6, 4)->default(0);
            $table->decimal('weighted_score_c', 6, 4)->default(0);
            
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();
            
            $table->unique(['test_session_id', 'question_id']);
            $table->index('test_session_id');
        });

        Schema::create('disc_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->unique()->constrained('disc_test_sessions')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            
            // Raw Scores (sum of ALL weighted scores per dimension)
            $table->decimal('d_raw_score', 8, 3)->default(0);
            $table->decimal('i_raw_score', 8, 3)->default(0);
            $table->decimal('s_raw_score', 8, 3)->default(0);
            $table->decimal('c_raw_score', 8, 3)->default(0);
            
            // Maximum possible scores
            $table->decimal('d_max_score', 8, 3)->default(0);
            $table->decimal('i_max_score', 8, 3)->default(0);
            $table->decimal('s_max_score', 8, 3)->default(0);
            $table->decimal('c_max_score', 8, 3)->default(0);
            
            // Percentage Scores (0-100)
            $table->decimal('d_percentage', 5, 2)->default(0);
            $table->decimal('i_percentage', 5, 2)->default(0);
            $table->decimal('s_percentage', 5, 2)->default(0);
            $table->decimal('c_percentage', 5, 2)->default(0);
            
            // Primary and Secondary Types
            $table->enum('primary_type', ['D', 'I', 'S', 'C'])->nullable();
            $table->decimal('primary_percentage', 5, 2)->nullable();
            $table->enum('secondary_type', ['D', 'I', 'S', 'C'])->nullable();
            $table->decimal('secondary_percentage', 5, 2)->nullable();
            
            // Graph Segments (1-7 scale)
            $table->integer('d_segment')->unsigned()->nullable();
            $table->integer('i_segment')->unsigned()->nullable();
            $table->integer('s_segment')->unsigned()->nullable();
            $table->integer('c_segment')->unsigned()->nullable();
            
            // Graph Data JSON
            $table->json('graph_data')->nullable();
            
            // Profile Description
            $table->text('profile_summary')->nullable();
            $table->json('full_profile')->nullable();
            
            // Detailed analysis
            $table->json('item_responses')->nullable();
            $table->json('dimension_breakdown')->nullable();
            
            $table->timestamps();
            
            $table->index(['candidate_id', 'primary_type', 'secondary_type']);
        });

        // Profile descriptions and config tables...
        Schema::create('disc_profile_descriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('dimension', ['D', 'I', 'S', 'C'])->unique();
            $table->string('name_en');
            $table->string('name_id');
            $table->text('description_en');
            $table->text('description_id');
            $table->json('traits_en');
            $table->json('traits_id');
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('work_style')->nullable();
            $table->timestamps();
            
            $table->index('dimension');
        });

        Schema::create('disc_test_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique();
            $table->text('config_value');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('config_key');
        });

        // Insert all data
        $this->insertCompleteDiscQuestions();
        $this->insertDiscProfiles();
        $this->insertDiscConfig();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_test_config');
        Schema::dropIfExists('disc_profile_descriptions');
        Schema::dropIfExists('disc_test_results');
        Schema::dropIfExists('disc_answers');
        Schema::dropIfExists('disc_test_sessions');
        Schema::dropIfExists('disc_questions');
    }

    /**
     * Insert ALL DISC questions from the complete dataset
     */
    private function insertCompleteDiscQuestions(): void
    {
        // Complete data extracted from your spreadsheet images
        $allQuestions = $this->getCompleteQuestionData();
        
        $timestamp = now();
        $insertData = [];
        
        foreach ($allQuestions as $index => $q) {
            // Determine primary dimension (highest absolute weight)
            $weights = [
                'D' => abs($q['weights']['D']),
                'I' => abs($q['weights']['I']),
                'S' => abs($q['weights']['S']),
                'C' => abs($q['weights']['C'])
            ];
            
            $primaryDim = array_keys($weights, max($weights))[0];
            $primaryStrength = $q['weights'][$primaryDim];
            
            $insertData[] = [
                'item_code' => $q['code'],
                'question_text_en' => $q['text'],
                'question_text_id' => $q['text_id'] ?? null,
                'weight_d' => $q['weights']['D'],
                'weight_i' => $q['weights']['I'],
                'weight_s' => $q['weights']['S'],
                'weight_c' => $q['weights']['C'],
                'primary_dimension' => $primaryDim,
                'primary_strength' => abs($primaryStrength),
                'order_number' => $index + 1,
                'is_core_16' => $q['is_core'] ?? false,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        // Insert in chunks
        $chunks = array_chunk($insertData, 50);
        foreach ($chunks as $chunk) {
            DB::table('disc_questions')->insert($chunk);
        }
    }

    /**
     * Get complete question data from spreadsheet
     */
    private function getCompleteQuestionData(): array
    {
        return [
            // Core 16 items (marked with is_core)
            ['code' => 'D1', 'text' => 'I put people under pressure.', 'weights' => ['D' => 0.7358, 'I' => -0.2224, 'S' => -0.8018, 'C' => -0.1825], 'is_core' => true],
            ['code' => 'D2', 'text' => 'I have a strong need for power.', 'weights' => ['D' => 0.6934, 'I' => -0.0433, 'S' => -0.7613, 'C' => -0.412], 'is_core' => true],
            ['code' => 'D3', 'text' => 'I try to outdo others.', 'weights' => ['D' => 0.4749, 'I' => -0.0763, 'S' => -0.6354, 'C' => -0.0928], 'is_core' => true],
            ['code' => 'D4', 'text' => 'I am always on the look out for ways to make money.', 'weights' => ['D' => 0.397, 'I' => -0.0116, 'S' => -0.4254, 'C' => -0.2985], 'is_core' => true],
            ['code' => 'I1', 'text' => 'I enjoy being part of a loud crowd.', 'weights' => ['D' => 0.142, 'I' => 0.6765, 'S' => -0.3591, 'C' => -0.7434], 'is_core' => true],
            ['code' => 'I2', 'text' => 'I want strangers to love me.', 'weights' => ['D' => -0.2084, 'I' => 0.5098, 'S' => 0.095, 'C' => -0.3346], 'is_core' => true],
            ['code' => 'I3', 'text' => 'I joke around a lot.', 'weights' => ['D' => -0.013, 'I' => 0.4548, 'S' => -0.1339, 'C' => -0.3701], 'is_core' => true],
            ['code' => 'I4', 'text' => 'I make lots of noise.', 'weights' => ['D' => 0.2961, 'I' => 0.5525, 'S' => -0.5285, 'C' => -0.668], 'is_core' => true],
            ['code' => 'S1', 'text' => 'I hesitate to criticize other people\'s ideas.', 'weights' => ['D' => -0.5269, 'I' => 0.1273, 'S' => 0.6263, 'C' => 0.1406], 'is_core' => true],
            ['code' => 'S2', 'text' => 'I value cooperation over competition.', 'weights' => ['D' => -0.4825, 'I' => 0.1843, 'S' => 0.5441, 'C' => 0.0763], 'is_core' => true],
            ['code' => 'S3', 'text' => 'I just want everyone to be equal.', 'weights' => ['D' => -0.4421, 'I' => 0.1835, 'S' => 0.4704, 'C' => 0.0569], 'is_core' => true],
            ['code' => 'S4', 'text' => 'I seldom toot my own horn.', 'weights' => ['D' => -0.2612, 'I' => -0.1909, 'S' => 0.3887, 'C' => 0.3309], 'is_core' => true],
            ['code' => 'C1', 'text' => 'I am emotionally reserved.', 'weights' => ['D' => -0.1637, 'I' => -0.6445, 'S' => 0.3095, 'C' => 0.7542], 'is_core' => true],
            ['code' => 'C2', 'text' => 'I read the fine print.', 'weights' => ['D' => -0.0746, 'I' => -0.3868, 'S' => -0.0472, 'C' => 0.574], 'is_core' => true],
            ['code' => 'C3', 'text' => 'I love order and regularity.', 'weights' => ['D' => -0.1189, 'I' => -0.4278, 'S' => 0.1641, 'C' => 0.5302], 'is_core' => true],
            ['code' => 'C4', 'text' => 'My first reaction to an idea is to see its flaws.', 'weights' => ['D' => 0.1896, 'I' => -0.4506, 'S' => -0.2252, 'C' => 0.464], 'is_core' => true],
            
            // Additional items from spreadsheet
            ['code' => 'X1', 'text' => 'I run to get where I am going faster.', 'weights' => ['D' => 0.3113, 'I' => 0.1123, 'S' => -0.3901, 'C' => -0.3055]],
            ['code' => 'X2', 'text' => 'I like to call people by their last names.', 'weights' => ['D' => 0.1547, 'I' => -0.0608, 'S' => -0.1903, 'C' => 0.0489]],
            ['code' => 'X3', 'text' => 'I boast about my virtues.', 'weights' => ['D' => 0.2581, 'I' => 0.1491, 'S' => -0.3749, 'C' => -0.2984]],
            ['code' => 'X4', 'text' => 'I call people out when they tell fake or exaggerated stories.', 'weights' => ['D' => 0.3784, 'I' => -0.1437, 'S' => -0.4214, 'C' => -0.0711]],
            ['code' => 'X5', 'text' => 'I demand the recognition I deserve.', 'weights' => ['D' => 0.3417, 'I' => 0.0824, 'S' => -0.4485, 'C' => -0.2974]],
            ['code' => 'X6', 'text' => 'I speed up to avoid being passed.', 'weights' => ['D' => 0.2984, 'I' => 0.0516, 'S' => -0.3386, 'C' => -0.2458]],
            ['code' => 'X7', 'text' => 'I laugh aloud.', 'weights' => ['D' => 0.0719, 'I' => 0.3955, 'S' => -0.1513, 'C' => -0.3856]],
            ['code' => 'X8', 'text' => 'I willing to try anything once.', 'weights' => ['D' => 0.1887, 'I' => 0.2539, 'S' => -0.2494, 'C' => -0.3837]],
            ['code' => 'X9', 'text' => 'I seek adventure.', 'weights' => ['D' => 0.2522, 'I' => 0.3142, 'S' => -0.3289, 'C' => -0.4874]],
            ['code' => 'X10', 'text' => 'I find humour in everything.', 'weights' => ['D' => -0.0894, 'I' => 0.4086, 'S' => -0.0263, 'C' => -0.3044]],
            ['code' => 'X11', 'text' => 'I love large parties.', 'weights' => ['D' => 0.1146, 'I' => 0.6706, 'S' => -0.3081, 'C' => -0.7583]],
            ['code' => 'X12', 'text' => 'I prefer to participate fully rather than view life from the sidelines.', 'weights' => ['D' => 0.2924, 'I' => 0.298, 'S' => -0.3571, 'C' => -0.5336]],
            ['code' => 'X13', 'text' => 'I phrase things diplomatically.', 'weights' => ['D' => -0.2556, 'I' => 0.0693, 'S' => 0.2194, 'C' => 0.1649]],
            ['code' => 'X14', 'text' => 'I have a good word for everyone.', 'weights' => ['D' => -0.262, 'I' => 0.3606, 'S' => 0.292, 'C' => -0.2933]],
            ['code' => 'X15', 'text' => 'I believe that others have good intentions.', 'weights' => ['D' => -0.2293, 'I' => 0.2918, 'S' => 0.258, 'C' => -0.1952]],
            ['code' => 'X16', 'text' => 'I would never cheat on my taxes.', 'weights' => ['D' => -0.1754, 'I' => -0.1068, 'S' => 0.2583, 'C' => 0.1953]],
            ['code' => 'X17', 'text' => 'I hate to seem pushy.', 'weights' => ['D' => -0.56, 'I' => 0.0782, 'S' => 0.5833, 'C' => 0.3251]],
            ['code' => 'X18', 'text' => 'If lots of people like something, I usually will too.', 'weights' => ['D' => -0.1811, 'I' => 0.2737, 'S' => 0.2224, 'C' => -0.2238]],
            ['code' => 'X19', 'text' => 'I avoid mistakes.', 'weights' => ['D' => -0.0641, 'I' => -0.3621, 'S' => 0.0732, 'C' => 0.4943]],
            ['code' => 'X20', 'text' => 'I am always guarded.', 'weights' => ['D' => -0.0101, 'I' => -0.563, 'S' => 0.1324, 'C' => 0.568]],
            ['code' => 'X21', 'text' => 'I don\'t ever litter.', 'weights' => ['D' => -0.1036, 'I' => -0.1255, 'S' => 0.1442, 'C' => 0.2213]],
            ['code' => 'X22', 'text' => 'I do not like small talk.', 'weights' => ['D' => 0.1722, 'I' => -0.5368, 'S' => -0.1134, 'C' => 0.47]],
            ['code' => 'X23', 'text' => 'I hate it when people want to make changes for no reason.', 'weights' => ['D' => -0.044, 'I' => -0.2758, 'S' => 0.1065, 'C' => 0.332]],
            ['code' => 'X24', 'text' => 'I leave parties early.', 'weights' => ['D' => -0.0149, 'I' => -0.5017, 'S' => 0.1921, 'C' => 0.4962]],
            ['code' => 'X25', 'text' => 'I prefer to make friends with people exactly like me.', 'weights' => ['D' => -0.0217, 'I' => -0.1721, 'S' => 0.063, 'C' => 0.1753]],
            ['code' => 'X26', 'text' => 'I like to do nice things for people even if I get no credit.', 'weights' => ['D' => -0.1855, 'I' => 0.1273, 'S' => 0.399, 'C' => -0.0611]],
            ['code' => 'X27', 'text' => 'I always remember when someone compliments me.', 'weights' => ['D' => -0.1093, 'I' => 0.1593, 'S' => 0.0827, 'C' => -0.0843]],
            ['code' => 'X28', 'text' => 'I don\'t like people who dress messy.', 'weights' => ['D' => 0.1807, 'I' => -0.1221, 'S' => -0.1881, 'C' => 0.0081]],
            ['code' => 'X29', 'text' => 'I lose my patience when I get tired.', 'weights' => ['D' => 0.1008, 'I' => -0.1086, 'S' => -0.1646, 'C' => -0.062]],
            ['code' => 'X30', 'text' => 'I get jealous of other people\'s friendships.', 'weights' => ['D' => -0.0779, 'I' => -0.0118, 'S' => 0.0933, 'C' => 0.1329]],
            ['code' => 'X31', 'text' => 'I think some people are just better than others.', 'weights' => ['D' => 0.3109, 'I' => -0.1668, 'S' => -0.3391, 'C' => 0.0091]],
            ['code' => 'A1', 'text' => 'Sometimes I have a hard time taking my eyes off of an attractive person.', 'weights' => ['D' => 0.0885, 'I' => 0.1551, 'S' => -0.1354, 'C' => -0.1931]],
            ['code' => 'A2', 'text' => 'My trust gets broken a lot.', 'weights' => ['D' => 0.0362, 'I' => -0.0985, 'S' => -0.0583, 'C' => 0.109]],
            ['code' => 'A3', 'text' => 'I ask why things really happened.', 'weights' => ['D' => 0.0887, 'I' => -0.1059, 'S' => -0.163, 'C' => 0.1685]]
        ];
    }

    /**
     * Insert DISC profile descriptions
     */
    private function insertDiscProfiles(): void
    {
        $profiles = [
            [
                'dimension' => 'D',
                'name_en' => 'Dominance',
                'name_id' => 'Dominan',
                'description_en' => 'Direct, Results-Oriented, Decisive, Competitive',
                'description_id' => 'Langsung, Berorientasi Hasil, Tegas, Kompetitif',
                'traits_en' => json_encode(['Assertive', 'Direct', 'Competitive', 'Results-focused', 'Decisive']),
                'traits_id' => json_encode(['Asertif', 'Langsung', 'Kompetitif', 'Fokus pada hasil', 'Tegas']),
                'strengths' => json_encode(['Leadership', 'Problem-solving', 'Decision-making', 'Goal achievement']),
                'weaknesses' => json_encode(['Impatient', 'Insensitive', 'Poor listener', 'Overly aggressive']),
                'work_style' => json_encode(['Fast-paced', 'Task-oriented', 'Independent', 'Challenge-seeking'])
            ],
            [
                'dimension' => 'I',
                'name_en' => 'Influence',
                'name_id' => 'Pengaruh',
                'description_en' => 'Outgoing, Enthusiastic, Optimistic, Social',
                'description_id' => 'Ramah, Antusias, Optimis, Sosial',
                'traits_en' => json_encode(['Enthusiastic', 'Optimistic', 'Social', 'Persuasive', 'Energetic']),
                'traits_id' => json_encode(['Antusias', 'Optimis', 'Sosial', 'Persuasif', 'Energik']),
                'strengths' => json_encode(['Communication', 'Motivation', 'Team building', 'Networking']),
                'weaknesses' => json_encode(['Disorganized', 'Impulsive', 'Lack of detail', 'Over-promising']),
                'work_style' => json_encode(['People-oriented', 'Collaborative', 'Creative', 'Flexible'])
            ],
            [
                'dimension' => 'S',
                'name_en' => 'Steadiness',
                'name_id' => 'Kestabilan',
                'description_en' => 'Patient, Loyal, Cooperative, Supportive',
                'description_id' => 'Sabar, Setia, Kooperatif, Mendukung',
                'traits_en' => json_encode(['Patient', 'Loyal', 'Supportive', 'Team-oriented', 'Reliable']),
                'traits_id' => json_encode(['Sabar', 'Setia', 'Mendukung', 'Berorientasi tim', 'Dapat diandalkan']),
                'strengths' => json_encode(['Listening', 'Teamwork', 'Patience', 'Loyalty']),
                'weaknesses' => json_encode(['Indecisive', 'Overly accommodating', 'Resistant to change', 'Passive']),
                'work_style' => json_encode(['Steady pace', 'Systematic', 'Supportive', 'Consistent'])
            ],
            [
                'dimension' => 'C',
                'name_en' => 'Conscientiousness',
                'name_id' => 'Ketelitian',
                'description_en' => 'Analytical, Systematic, Accurate, Quality-Focused',
                'description_id' => 'Analitis, Sistematis, Akurat, Fokus pada Kualitas',
                'traits_en' => json_encode(['Analytical', 'Detailed', 'Systematic', 'Quality-focused', 'Careful']),
                'traits_id' => json_encode(['Analitis', 'Detail', 'Sistematis', 'Fokus kualitas', 'Hati-hati']),
                'strengths' => json_encode(['Analysis', 'Planning', 'Quality control', 'Problem solving']),
                'weaknesses' => json_encode(['Perfectionist', 'Critical', 'Slow decision-making', 'Inflexible']),
                'work_style' => json_encode(['Methodical', 'Precise', 'Reserved', 'Logical'])
            ]
        ];

        $timestamp = now();
        foreach ($profiles as &$profile) {
            $profile['created_at'] = $timestamp;
            $profile['updated_at'] = $timestamp;
        }

        DB::table('disc_profile_descriptions')->insert($profiles);
    }

    /**
     * Insert DISC test configuration
     */
    private function insertDiscConfig(): void
    {
        $configs = [
            [
                'config_key' => 'segment_thresholds',
                'config_value' => json_encode([
                    1 => [0, 14],
                    2 => [15, 28],
                    3 => [29, 42],
                    4 => [43, 57],
                    5 => [58, 71],
                    6 => [72, 85],
                    7 => [86, 100]
                ]),
                'description' => 'Percentage ranges for each segment (1-7) in the graph'
            ],
            [
                'config_key' => 'test_versions',
                'config_value' => json_encode([
                    'core_16' => 'DISC Test 1.0 - 16 core questions',
                    'full_50' => 'DISC Test Complete - All questions'
                ]),
                'description' => 'Available test versions'
            ],
            [
                'config_key' => 'max_scores_will_be_calculated',
                'config_value' => json_encode([
                    'note' => 'Maximum scores are calculated dynamically based on positive weights only'
                ]),
                'description' => 'Max scores calculated at runtime based on active questions'
            ]
        ];

        $timestamp = now();
        foreach ($configs as &$config) {
            $config['created_at'] = $timestamp;
            $config['updated_at'] = $timestamp;
        }

        DB::table('disc_test_config')->insert($configs);
    }
};