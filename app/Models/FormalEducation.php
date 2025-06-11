namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'education_level',
        'institution_name',
        'major',
        'start_month',
        'start_year',
        'end_month',
        'end_year',
        'gpa'
    ];

    protected $casts = [
        'start_month' => 'integer',
        'start_year' => 'integer',
        'end_month' => 'integer',
        'end_year' => 'integer',
        'gpa' => 'decimal:2'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByLevel($query, $level)
    {
        return $query->where('education_level', $level);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('end_year', 'desc')->orderBy('end_month', 'desc');
    }

    // Accessors
    public function getPeriodAttribute()
    {
        $start = $this->start_year . ($this->start_month ? '/' . $this->start_month : '');
        $end = $this->end_year . ($this->end_month ? '/' . $this->end_month : '');
        return $start . ' - ' . $end;
    }
}
