namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LanguageSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'language',
        'speaking_level',
        'writing_level',
        'other_language_name'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    // Accessors
    public function getLanguageNameAttribute()
    {
        return $this->language === 'Lainnya' ? $this->other_language_name : $this->language;
    }
}
