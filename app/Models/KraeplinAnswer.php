<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraeplinAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'column_number',
        'row_number',
        'question_value_1',
        'question_value_2',
        'correct_answer',
        'user_answer',
        'is_correct',
        'time_spent_seconds'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    // Relationships
    public function testSession()
    {
        return $this->belongsTo(KraeplinTestSession::class, 'test_session_id');
    }

    // Scopes
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopeByColumn($query, $columnNumber)
    {
        return $query->where('column_number', $columnNumber);
    }

    public function scopeByRow($query, $rowNumber)
    {
        return $query->where('row_number', $rowNumber);
    }

    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('column_number')->orderBy('row_number');
    }

    // Accessors
    public function getQuestionStringAttribute()
    {
        return $this->question_value_1 . ' + ' . $this->question_value_2;
    }

    public function getAnswerStatusAttribute()
    {
        return $this->is_correct ? 'correct' : 'incorrect';
    }

    public function getAnswerStatusLabelAttribute()
    {
        return $this->is_correct ? 'Benar' : 'Salah';
    }

    public function getAnswerStatusColorAttribute()
    {
        return $this->is_correct ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
    }

    public function getAnswerStatusIconAttribute()
    {
        return $this->is_correct ? '✅' : '❌';
    }

    public function getFormattedTimeSpentAttribute()
    {
        return $this->time_spent_seconds . 's';
    }

    // Helper methods
    public function isCorrect()
    {
        return $this->is_correct;
    }

    public function isIncorrect()
    {
        return !$this->is_correct;
    }

    public function calculateCorrectAnswer()
    {
        return ($this->question_value_1 + $this->question_value_2) % 10;
    }

    public function verifyAnswer()
    {
        return $this->user_answer === $this->correct_answer;
    }

    // Static methods for analysis
    public static function getAccuracyBySession($sessionId)
    {
        $answers = self::where('test_session_id', $sessionId);
        $total = $answers->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $correct = $answers->where('is_correct', true)->count();
        return round(($correct / $total) * 100, 2);
    }

    public static function getAccuracyByColumn($sessionId, $columnNumber)
    {
        $answers = self::where('test_session_id', $sessionId)
            ->where('column_number', $columnNumber);
        $total = $answers->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $correct = $answers->where('is_correct', true)->count();
        return round(($correct / $total) * 100, 2);
    }

    public static function getSpeedByColumn($sessionId, $columnNumber)
    {
        return self::where('test_session_id', $sessionId)
            ->where('column_number', $columnNumber)
            ->count();
    }

    public static function getColumnPerformance($sessionId)
    {
        $performance = [];
        
        for ($col = 1; $col <= 32; $col++) {
            $answers = self::where('test_session_id', $sessionId)
                ->where('column_number', $col);
            
            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
            
            $performance[] = [
                'column' => $col,
                'total_answered' => $total,
                'correct_answers' => $correct,
                'accuracy' => $accuracy
            ];
        }
        
        return $performance;
    }
}