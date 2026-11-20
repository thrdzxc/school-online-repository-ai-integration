<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiCriterionScore extends Model
{
    protected $fillable = ['ai_evaluation_id','rubric_criterion_id','score','feedback'];
    protected function casts(): array { return ['score'=>'decimal:2']; }
    public function evaluation(){ return $this->belongsTo(AiEvaluation::class,'ai_evaluation_id'); }
    public function criterion(){ return $this->belongsTo(RubricCriterion::class,'rubric_criterion_id'); }
}
