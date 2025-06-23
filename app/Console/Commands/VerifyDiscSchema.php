<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DiscAnswer;

class VerifyDiscSchema extends Command
{
    protected $signature = 'disc:verify-schema';
    protected $description = 'Verify DISC database schema and fix common issues';

    public function handle()
    {
        $this->info('🔍 Verifying DISC database schema...');
        
        // Check if disc_answers table exists
        if (!Schema::hasTable('disc_answers')) {
            $this->error('❌ Table disc_answers does not exist!');
            $this->info('Run: php artisan migrate');
            return 1;
        }
        
        $this->info('✅ Table disc_answers exists');
        
        // Check required columns
        $requiredColumns = [
            'id', 'test_session_id', 'question_id', 'item_code', 'response',
            'weighted_score_d', 'weighted_score_i', 'weighted_score_s', 'weighted_score_c',
            'time_spent_seconds', 'created_at', 'updated_at'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('disc_answers', $column)) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            $this->error('❌ Missing columns: ' . implode(', ', $missingColumns));
            $this->info('Please check your migration file and run: php artisan migrate:fresh');
            return 1;
        }
        
        $this->info('✅ All required columns exist');
        
        // Check column types
        $columns = DB::select("DESCRIBE disc_answers");
        $columnInfo = collect($columns)->keyBy('Field');
        
        $expectedTypes = [
            'weighted_score_d' => 'decimal',
            'weighted_score_i' => 'decimal',
            'weighted_score_s' => 'decimal',
            'weighted_score_c' => 'decimal'
        ];
        
        foreach ($expectedTypes as $column => $expectedType) {
            $actualType = $columnInfo[$column]->Type ?? 'unknown';
            if (!str_contains(strtolower($actualType), $expectedType)) {
                $this->warn("⚠️  Column {$column} type is {$actualType}, expected {$expectedType}");
            } else {
                $this->info("✅ Column {$column} type is correct: {$actualType}");
            }
        }
        
        // Test model creation
        try {
            $testData = [
                'test_session_id' => 1,
                'question_id' => 1,
                'item_code' => 'TEST',
                'response' => 3,
                'weighted_score_d' => 1.5000,
                'weighted_score_i' => 0.0000,
                'weighted_score_s' => -0.5000,
                'weighted_score_c' => 2.0000,
                'time_spent_seconds' => 30
            ];
            
            $answer = new DiscAnswer($testData);
            $this->info('✅ Model can be instantiated with test data');
            
            // Check if attributes are accessible
            $scores = $answer->getWeightedScoresAttribute();
            $this->info('✅ Weighted scores accessor works: ' . json_encode($scores));
            
        } catch (\Exception $e) {
            $this->error('❌ Model test failed: ' . $e->getMessage());
            return 1;
        }
        
        // Check for existing data integrity
        $answersCount = DB::table('disc_answers')->count();
        $this->info("📊 Current answers in database: {$answersCount}");
        
        if ($answersCount > 0) {
            // Check for null values in weighted score columns
            $nullCounts = [];
            foreach (['weighted_score_d', 'weighted_score_i', 'weighted_score_s', 'weighted_score_c'] as $column) {
                $count = DB::table('disc_answers')->whereNull($column)->count();
                if ($count > 0) {
                    $nullCounts[$column] = $count;
                }
            }
            
            if (!empty($nullCounts)) {
                $this->warn('⚠️  Found NULL values in weighted score columns:');
                foreach ($nullCounts as $column => $count) {
                    $this->warn("   {$column}: {$count} NULL values");
                }
                
                if ($this->confirm('Do you want to fix NULL values by setting them to 0?')) {
                    foreach ($nullCounts as $column => $count) {
                        DB::table('disc_answers')->whereNull($column)->update([$column => 0]);
                        $this->info("✅ Fixed {$count} NULL values in {$column}");
                    }
                }
            } else {
                $this->info('✅ No NULL values found in weighted score columns');
            }
        }
        
        // Clear model cache
        if (method_exists(DiscAnswer::class, 'flushEventListeners')) {
            DiscAnswer::flushEventListeners();
        }
        
        // Clear application cache
        $this->call('cache:clear');
        $this->call('config:clear');
        
        $this->info('🎉 DISC schema verification completed successfully!');
        return 0;
    }
}