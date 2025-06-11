namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PersonalData extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'full_name',
        'birth_place',
        'birth_date',
        'age',
        'gender',
        'religion',
        'ethnicity',
        'marital_status',
        'email',
        'current_address',
        'ktp_address',
        'phone_number',
        'residence_status',
        'height_cm',
        'weight_kg',
        'vaccination_status'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'age' => 'integer',
        'height_cm' => 'integer',
        'weight_kg' => 'integer'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Accessors
    public function getCalculatedAgeAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : $this->age;
    }

    public function getBmiAttribute()
    {
        if ($this->height_cm && $this->weight_kg) {
            $heightInMeters = $this->height_cm / 100;
            return round($this->weight_kg / ($heightInMeters * $heightInMeters), 2);
        }
        return null;
    }
}
