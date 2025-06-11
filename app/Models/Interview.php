namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'interviewer_id',
        'interview_date',
        'interview_time',
        'interview_type',
        'location',
        'status',
        'notes',
        'score'
    ];

    protected $casts = [
        'interview_date' => 'date',
        'interview_time' => 'datetime',
        'score' => 'integer'
    ];

    // Constants
    const TYPE_PHONE = 'phone';
    const TYPE_VIDEO = 'video';
    const TYPE_IN_PERSON = 'in-person';

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RESCHEDULED = 'rescheduled';

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('interview_date', '>=', today())
                    ->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('interview_date', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('interview_type', $type);
    }
}
