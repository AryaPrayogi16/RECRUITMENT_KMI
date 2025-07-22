<?php

namespace App\Services;

use App\Models\{Candidate, KraeplinTestSession, Disc3DTestSession};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CodeGenerationService
{
    /**
     * Generate unique candidate code - SHORT FORMAT (NUMBERS ONLY)
     * Format: KMI + YEAR + TIMESTAMP_4 + HASH_4 + RANDOM_3 = 15 characters
     */
    public static function generateCandidateCode(): string
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Short approach: timestamp + random + microtime
            $timestamp = time();
            $random = mt_rand(100, 999);
            $microtime = substr(str_replace('.', '', microtime(true)), -3); // Last 3 digits
            
            // Create short numeric hash
            $uniqueString = $timestamp . $random . $microtime . $attempt;
            $numericHash = substr(abs(crc32($uniqueString)), 0, 4); // 4-digit hash
            
            // Format: KMI + YEAR + TIMESTAMP_LAST_4 + HASH_4 + RANDOM_3
            // Total: KMI + 4 + 4 + 4 + 3 = 18 characters
            $code = 'KMI' . date('Y') . 
                   substr($timestamp, -4) . 
                   str_pad($numericHash, 4, '0', STR_PAD_LEFT) . 
                   str_pad($random, 3, '0', STR_PAD_LEFT);
                   
            // Ensure uniqueness in database
            $exists = DB::table('candidates')
                ->where('candidate_code', $code)
                ->exists();
                
            if (!$exists) {
                Log::info('Generated unique candidate code', [
                    'code' => $code,
                    'attempt' => $attempt,
                    'length' => strlen($code)
                ]);
                return $code;
            }
            
            // Add small delay to avoid race condition
            usleep(1000); // 1ms delay
            
        } while ($exists && $attempt < $maxAttempts);
        
        // Fallback: use pure timestamp + random approach
        $fallbackTimestamp = substr(time(), -6);
        $fallbackRandom = mt_rand(1000, 9999);
        $fallbackCode = 'KMI' . date('Y') . $fallbackTimestamp . $fallbackRandom;
        
        Log::warning('Used fallback candidate code generation', [
            'code' => $fallbackCode,
            'attempts' => $attempt
        ]);
        
        return $fallbackCode;
    }

    /**
     * Generate Kraeplin test code based on candidate code - SHORT FORMAT
     * Format: KT + YEAR + CANDIDATE_HASH_4 + TEST_HASH_3 + TIMESTAMP_LAST_2 + RANDOM_2 = 17 characters
     */
    public static function generateKraeplinTestCode(string $candidateCode): string
    {
        // Extract numeric parts from candidate code (take last 6 digits)
        $candidateNumeric = preg_replace('/[^0-9]/', '', $candidateCode);
        $candidateHash = substr(abs(crc32($candidateCode)), 0, 4); // 4-digit hash
        
        $timestamp = time();
        $random = mt_rand(10, 99); // 2-digit random
        
        // Create unique combination for additional hash
        $uniqueString = $candidateCode . $timestamp . 'KRAEPLIN';
        $testHash = substr(abs(crc32($uniqueString)), 0, 3); // 3-digit hash
        
        // Format: KT + YEAR + CANDIDATE_HASH_4 + TEST_HASH_3 + TIMESTAMP_LAST_2 + RANDOM_2
        // Total: KT + 4 + 4 + 3 + 2 + 2 = 17 characters
        $testCode = 'KT' . date('Y') . 
                   str_pad($candidateHash, 4, '0', STR_PAD_LEFT) . 
                   str_pad($testHash, 3, '0', STR_PAD_LEFT) . 
                   substr($timestamp, -2) . 
                   str_pad($random, 2, '0', STR_PAD_LEFT);
        
        // Verify uniqueness (should be guaranteed by design, but double-check)
        $maxAttempts = 5;
        $attempt = 0;
        
        do {
            $attempt++;
            $currentCode = $testCode . ($attempt > 1 ? str_pad($attempt, 1, '0', STR_PAD_LEFT) : '');
            
            $exists = DB::table('kraeplin_test_sessions')
                ->where('test_code', $currentCode)
                ->exists();
                
            if (!$exists) {
                Log::info('Generated Kraeplin test code', [
                    'candidate_code' => $candidateCode,
                    'test_code' => $currentCode,
                    'length' => strlen($currentCode),
                    'attempt' => $attempt
                ]);
                return $currentCode;
            }
            
            usleep(500); // 0.5ms delay
            
        } while ($exists && $attempt < $maxAttempts);
        
        // This should theoretically never happen with our hash-based approach
        throw new \Exception("Failed to generate unique Kraeplin test code after {$maxAttempts} attempts");
    }

    /**
     * Generate DISC test code based on candidate code - SHORT FORMAT
     * Format: D3D + YEAR + CANDIDATE_HASH_4 + TEST_HASH_3 + TIMESTAMP_LAST_2 + RANDOM_2 = 18 characters
     */
    public static function generateDiscTestCode(string $candidateCode): string
    {
        // Extract numeric parts from candidate code (take last 6 digits)
        $candidateNumeric = preg_replace('/[^0-9]/', '', $candidateCode);
        $candidateHash = substr(abs(crc32($candidateCode)), 0, 4); // 4-digit hash
        
        $timestamp = time();
        $random = mt_rand(10, 99); // 2-digit random
        
        // Create unique combination for additional hash
        $uniqueString = $candidateCode . $timestamp . 'DISC3D';
        $testHash = substr(abs(crc32($uniqueString)), 0, 3); // 3-digit hash
        
        // Format: D3D + YEAR + CANDIDATE_HASH_4 + TEST_HASH_3 + TIMESTAMP_LAST_2 + RANDOM_2
        // Total: D3D + 4 + 4 + 3 + 2 + 2 = 18 characters
        $testCode = 'D3D' . date('Y') . 
                   str_pad($candidateHash, 4, '0', STR_PAD_LEFT) . 
                   str_pad($testHash, 3, '0', STR_PAD_LEFT) . 
                   substr($timestamp, -2) . 
                   str_pad($random, 2, '0', STR_PAD_LEFT);
        
        // Verify uniqueness
        $maxAttempts = 5;
        $attempt = 0;
        
        do {
            $attempt++;
            $currentCode = $testCode . ($attempt > 1 ? str_pad($attempt, 1, '0', STR_PAD_LEFT) : '');
            
            $exists = DB::table('disc_3d_test_sessions')
                ->where('test_code', $currentCode)
                ->exists();
                
            if (!$exists) {
                Log::info('Generated DISC test code', [
                    'candidate_code' => $candidateCode,
                    'test_code' => $currentCode,
                    'length' => strlen($currentCode),
                    'attempt' => $attempt
                ]);
                return $currentCode;
            }
            
            usleep(500); // 0.5ms delay
            
        } while ($exists && $attempt < $maxAttempts);
        
        // This should theoretically never happen with our hash-based approach
        throw new \Exception("Failed to generate unique DISC test code after {$maxAttempts} attempts");
    }

    /**
     * Validate code format and uniqueness
     */
    public static function validateCodeUniqueness(string $code, string $type): bool
    {
        $table = match($type) {
            'candidate' => 'candidates',
            'kraeplin' => 'kraeplin_test_sessions',
            'disc' => 'disc_3d_test_sessions',
            default => throw new \InvalidArgumentException("Invalid code type: {$type}")
        };
        
        $column = $type === 'candidate' ? 'candidate_code' : 'test_code';
        
        return !DB::table($table)->where($column, $code)->exists();
    }

    /**
     * Emergency code generation (if all else fails) - NUMBERS ONLY
     */
    public static function generateEmergencyCode(string $prefix = 'EMG'): string
    {
        $timestamp = time();
        $random = mt_rand(100000, 999999);
        $microtime = substr(str_replace('.', '', microtime(true)), -6);
        $hash = substr(abs(crc32($timestamp . $random . $microtime)), 0, 8);
        
        return $prefix . date('Y') . $timestamp . $random . $microtime . $hash;
    }

    /**
     * Helper: Convert string to numeric hash
     */
    private static function stringToNumericHash(string $input, int $length = 8): string
    {
        $hash = abs(crc32($input));
        return str_pad(substr($hash, 0, $length), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Helper: Generate secure random numeric string
     */
    private static function generateSecureNumeric(int $length): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    /**
     * Batch generate codes for testing purposes
     */
    public static function batchGenerateCodes(int $count = 100): array
    {
        $results = [
            'candidates' => [],
            'kraeplins' => [],
            'discs' => [],
            'duplicates' => [],
            'stats' => []
        ];
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < $count; $i++) {
            try {
                // Generate candidate code
                $candidateCode = self::generateCandidateCode();
                
                if (in_array($candidateCode, $results['candidates'])) {
                    $results['duplicates'][] = $candidateCode;
                } else {
                    $results['candidates'][] = $candidateCode;
                }
                
                // Generate test codes based on candidate
                $kraeplinCode = self::generateKraeplinTestCode($candidateCode);
                $discCode = self::generateDiscTestCode($candidateCode);
                
                $results['kraeplins'][] = $kraeplinCode;
                $results['discs'][] = $discCode;
                
            } catch (\Exception $e) {
                Log::error('Error in batch code generation', [
                    'iteration' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $endTime = microtime(true);
        
        $results['stats'] = [
            'total_generated' => $count,
            'unique_candidates' => count(array_unique($results['candidates'])),
            'unique_kraeplins' => count(array_unique($results['kraeplins'])),
            'unique_discs' => count(array_unique($results['discs'])),
            'duplicates_count' => count($results['duplicates']),
            'generation_time' => round($endTime - $startTime, 4),
            'codes_per_second' => round($count / ($endTime - $startTime), 2)
        ];
        
        return $results;
    }
}