<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraeplinTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'candidate_id',
        'total_questions_answered',
        'total_correct_answers',
        'total_wrong_answers',
        'accuracy_percentage',
        'average_speed_per_column',
        'overall_score',
        'performance_category',
        'column_accuracy',
        'column_speed',
        'column_correct_count',
        'column_answered_count',
        'column_avg_time'
    ];

    protected $casts = [
        'accuracy_percentage' => 'decimal:2',
        'average_speed_per_column' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'column_accuracy' => 'array',
        'column_speed' => 'array',
        'column_correct_count' => 'array',
        'column_answered_count' => 'array',
        'column_avg_time' => 'array'
    ];

    protected static function boot()
        {
            parent::boot();
            
            // Force cast JSON fields when retrieved
            static::retrieved(function ($model) {
                $jsonFields = ['column_correct_count', 'column_answered_count', 'column_avg_time', 'column_accuracy'];
                
                foreach ($jsonFields as $field) {
                    if (is_string($model->$field)) {
                        $model->$field = json_decode($model->$field, true);
                    }
                }
            });
        }

        /**
         * Get column_correct_count attribute - force array
         */
        public function getColumnCorrectCountAttribute($value)
        {
            if (is_string($value)) {
                return json_decode($value, true);
            }
            return $value ?? array_fill(0, 32, 0);
        }

        /**
         * Get column_answered_count attribute - force array
         */
        public function getColumnAnsweredCountAttribute($value)
        {
            if (is_string($value)) {
                return json_decode($value, true);
            }
            return $value ?? array_fill(0, 32, 0);
        }

        /**
         * Get column_avg_time attribute - force array
         */
        public function getColumnAvgTimeAttribute($value)
        {
            if (is_string($value)) {
                return json_decode($value, true);
            }
            return $value ?? array_fill(0, 32, 0);
        }

        /**
         * Get column_accuracy attribute - force array
         */
        public function getColumnAccuracyAttribute($value)
        {
            if (is_string($value)) {
                return json_decode($value, true);
            }
            return $value ?? array_fill(0, 32, 0);
        }

    // Performance category constants
    const CATEGORY_EXCELLENT = 'excellent';
    const CATEGORY_GOOD = 'good';
    const CATEGORY_AVERAGE = 'average';
    const CATEGORY_BELOW_AVERAGE = 'below_average';
    const CATEGORY_POOR = 'poor';

    // Relationships
    public function testSession()
    {
        return $this->belongsTo(KraeplinTestSession::class, 'test_session_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByPerformanceCategory($query, $category)
    {
        return $query->where('performance_category', $category);
    }

    public function scopeExcellent($query)
    {
        return $query->where('performance_category', self::CATEGORY_EXCELLENT);
    }

    public function scopeGood($query)
    {
        return $query->where('performance_category', self::CATEGORY_GOOD);
    }

    public function scopeAverage($query)
    {
        return $query->where('performance_category', self::CATEGORY_AVERAGE);
    }

    public function scopeBelowAverage($query)
    {
        return $query->where('performance_category', self::CATEGORY_BELOW_AVERAGE);
    }

    public function scopePoor($query)
    {
        return $query->where('performance_category', self::CATEGORY_POOR);
    }

    public function scopeByScoreRange($query, $min, $max)
    {
        return $query->whereBetween('overall_score', [$min, $max]);
    }

    public function scopeHighPerformers($query)
    {
        return $query->where('overall_score', '>=', 80);
    }

    public function scopeLowPerformers($query)
    {
        return $query->where('overall_score', '<', 60);
    }

    // Accessors
    public function getPerformanceCategoryLabelAttribute()
    {
        $labels = [
            self::CATEGORY_EXCELLENT => 'Sangat Baik',
            self::CATEGORY_GOOD => 'Baik',
            self::CATEGORY_AVERAGE => 'Rata-rata',
            self::CATEGORY_BELOW_AVERAGE => 'Di Bawah Rata-rata',
            self::CATEGORY_POOR => 'Kurang'
        ];

        return $labels[$this->performance_category] ?? 'Tidak Diketahui';
    }

    public function getGradeAttribute()
    {
        if ($this->overall_score >= 85) return 'A';
        if ($this->overall_score >= 75) return 'B';
        if ($this->overall_score >= 65) return 'C';
        if ($this->overall_score >= 50) return 'D';
        return 'E';
    }

    public function getGradeColorAttribute()
    {
        $colors = [
            'A' => 'text-green-600 bg-green-100',
            'B' => 'text-blue-600 bg-blue-100',
            'C' => 'text-yellow-600 bg-yellow-100',
            'D' => 'text-orange-600 bg-orange-100',
            'E' => 'text-red-600 bg-red-100'
        ];

        return $colors[$this->grade] ?? 'text-gray-600 bg-gray-100';
    }

    public function getFormattedOverallScoreAttribute()
    {
        return number_format($this->overall_score, 1);
    }

    public function getFormattedAccuracyAttribute()
    {
        return number_format($this->accuracy_percentage, 1) . '%';
    }

    public function getCompletionRateAttribute()
    {
        $maxQuestions = 32 * 26; // 32 columns * 26 questions per column
        return round(($this->total_questions_answered / $maxQuestions) * 100, 2);
    }

    public function getFormattedCompletionRateAttribute()
    {
        return number_format($this->completion_rate, 1) . '%';
    }

    public function getFormattedSpeedAttribute()
    {
        return number_format($this->average_speed_per_column, 1) . ' soal/kolom';
    }

/**
 * Calculate average time per question across all columns
 */
public function getAverageTimePerQuestionAttribute()
{
    $avgTimeArray = $this->getAvgTimeChartData();
    
    // Filter out zero values (columns that weren't attempted)
    $validTimes = array_filter($avgTimeArray, function($time) { 
        return $time > 0; 
    });
    
    // Calculate average only from columns that were actually attempted
    return count($validTimes) > 0 ? 
        array_sum($validTimes) / count($validTimes) : 0;
}

    /**
     * Get formatted average time per question
     */
    public function getFormattedAverageTimeAttribute()
    {
        return number_format($this->average_time_per_question, 2) . ' detik/kolom';
    }

    /**
     * Get total time spent on test (calculated from individual answers)
     */
    public function getTotalTimeSpentAttribute()
    {
        return $this->total_questions_answered * $this->average_time_per_question;
    }

    /**
     * Get formatted total time spent
     */
    public function getFormattedTotalTimeSpentAttribute()
    {
        $totalSeconds = $this->total_time_spent;
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }


    // Helper methods for chart data (sesuai dengan requirement: 3 aspek penilaian)
    
    /**
     * 1. Jumlah benar menjawab per kolom (untuk line chart Y axis)
     */
    public function getCorrectCountChartData()
    {
        return $this->column_correct_count ?? array_fill(0, 32, 0);
    }

    /**
     * 2. Kecepatan menjawab - rata-rata waktu per soal per kolom
     */
    public function getAvgTimeChartData()
    {
        return $this->column_avg_time ?? array_fill(0, 32, 0);
    }

    /**
     * 3. Jumlah soal yang dijawab per kolom
     */
    public function getAnsweredCountChartData()
    {
        return $this->column_answered_count ?? array_fill(0, 32, 0);
    }

    /**
     * Helper untuk akurasi per kolom (bonus data)
     */
    public function getAccuracyChartData()
    {
        return $this->column_accuracy ?? array_fill(0, 32, 0);
    }

    /**
     * Helper untuk kecepatan (jumlah soal per kolom) - alias untuk backward compatibility
     */
    public function getSpeedChartData()
    {
        return $this->column_speed ?? array_fill(0, 32, 0);
    }

    /**
     * Generate labels untuk X axis (1-32 kolom)
     */
    public function getColumnLabels()
    {
        return array_map(function($i) { return (string)$i; }, range(1, 32));
    }

    // Helper methods
    public function isExcellent()
    {
        return $this->performance_category === self::CATEGORY_EXCELLENT;
    }

    public function isGood()
    {
        return $this->performance_category === self::CATEGORY_GOOD;
    }

    public function isAverage()
    {
        return $this->performance_category === self::CATEGORY_AVERAGE;
    }

    public function isBelowAverage()
    {
        return $this->performance_category === self::CATEGORY_BELOW_AVERAGE;
    }

    public function isPoor()
    {
        return $this->performance_category === self::CATEGORY_POOR;
    }

    public function getScoreInterpretation()
    {
        $interpretations = [
            self::CATEGORY_EXCELLENT => 'Kandidat menunjukkan performa yang sangat baik dengan tingkat akurasi dan kecepatan yang tinggi. Kemampuan konsentrasi dan konsistensi sangat baik.',
            self::CATEGORY_GOOD => 'Kandidat menunjukkan performa yang baik dengan tingkat akurasi dan kecepatan yang memadai. Kemampuan konsentrasi dan konsistensi baik.',
            self::CATEGORY_AVERAGE => 'Kandidat menunjukkan performa rata-rata dengan tingkat akurasi dan kecepatan yang cukup. Kemampuan konsentrasi dan konsistensi dalam batas normal.',
            self::CATEGORY_BELOW_AVERAGE => 'Kandidat menunjukkan performa di bawah rata-rata. Perlu peningkatan dalam hal akurasi, kecepatan, atau konsistensi.',
            self::CATEGORY_POOR => 'Kandidat menunjukkan performa yang kurang. Diperlukan evaluasi lebih lanjut terkait kemampuan konsentrasi dan ketelitian.'
        ];

        return $interpretations[$this->performance_category] ?? 'Interpretasi tidak tersedia.';
    }

    public function getStrengths()
    {
        $strengths = [];

        if ($this->accuracy_percentage >= 80) {
            $strengths[] = 'Tingkat akurasi tinggi';
        }

        if ($this->average_speed_per_column >= 15) {
            $strengths[] = 'Kecepatan kerja baik';
        }

        if ($this->completion_rate >= 70) {
            $strengths[] = 'Tingkat penyelesaian tinggi';
        }

        if ($this->overall_score >= 75) {
            $strengths[] = 'Performa keseluruhan baik';
        }

        return $strengths;
    }

    public function getWeaknesses()
    {
        $weaknesses = [];

        if ($this->accuracy_percentage < 60) {
            $weaknesses[] = 'Tingkat akurasi perlu ditingkatkan';
        }

        if ($this->average_speed_per_column < 10) {
            $weaknesses[] = 'Kecepatan kerja perlu ditingkatkan';
        }

        if ($this->completion_rate < 50) {
            $weaknesses[] = 'Tingkat penyelesaian rendah';
        }

        if ($this->overall_score < 60) {
            $weaknesses[] = 'Performa keseluruhan perlu perbaikan';
        }

        return $weaknesses;
    }

    public function getRecommendations()
    {
        $recommendations = [];

        if ($this->isExcellent() || $this->isGood()) {
            $recommendations[] = 'Kandidat memiliki potensi yang baik untuk posisi yang membutuhkan konsentrasi dan ketelitian tinggi.';
        }

        if ($this->accuracy_percentage < 70) {
            $recommendations[] = 'Perlu pelatihan untuk meningkatkan akurasi dan ketelitian dalam bekerja.';
        }

        if ($this->average_speed_per_column < 12) {
            $recommendations[] = 'Dapat diberikan waktu adaptasi lebih untuk meningkatkan kecepatan kerja.';
        }

        if ($this->isPoor()) {
            $recommendations[] = 'Diperlukan evaluasi tambahan atau pelatihan intensif sebelum penempatan kerja.';
        }

        return $recommendations;
    }

    // Static methods for analytics
    public static function getAverageScore()
    {
        return self::avg('overall_score');
    }

    public static function getPerformanceDistribution()
    {
        return self::selectRaw('performance_category, COUNT(*) as count')
            ->groupBy('performance_category')
            ->pluck('count', 'performance_category')
            ->toArray();
    }

    public static function getTopPerformers($limit = 10)
    {
        return self::with(['candidate.personalData'])
            ->orderBy('overall_score', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getAverageByCategory()
    {
        return self::selectRaw('performance_category, AVG(overall_score) as avg_score, COUNT(*) as count')
            ->groupBy('performance_category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->performance_category => [
                    'average_score' => round($item->avg_score, 2),
                    'count' => $item->count
                ]];
            });
    }

    public function debugChartData()
{
    return [
        'column_correct_count' => $this->column_correct_count,
        'column_answered_count' => $this->column_answered_count,
        'column_avg_time' => $this->column_avg_time,
        'column_accuracy' => $this->column_accuracy,
        'has_correct_count' => !is_null($this->column_correct_count),
        'has_answered_count' => !is_null($this->column_answered_count),
        'has_avg_time' => !is_null($this->column_avg_time),
        'has_accuracy' => !is_null($this->column_accuracy),
    ];
}
}

