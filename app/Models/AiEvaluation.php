<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiEvaluation extends Model
{
    protected $fillable = [
        'submission_id','provider','model','recommended_score','rubric_breakdown','summary','strengths','improvements',
        'confidence','raw_response','status','error_message','is_demo','evaluated_at'
    ];
    protected function casts(): array {
        return [
            'recommended_score'=>'decimal:2','confidence'=>'decimal:2','rubric_breakdown'=>'array',
            'strengths'=>'array','improvements'=>'array','raw_response'=>'array','is_demo'=>'boolean','evaluated_at'=>'datetime'
        ];
    }
    public function submission(){ return $this->belongsTo(Submission::class); }
    // Retained for backward compatibility with older assessments created by the previous rubric builder.
    public function criterionScores(){ return $this->hasMany(AiCriterionScore::class); }
}
