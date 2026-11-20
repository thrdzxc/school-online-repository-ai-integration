<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RubricCriterion extends Model
{
    protected $fillable = ['activity_id','name','description','max_points','sort_order'];
    protected function casts(): array { return ['max_points'=>'decimal:2']; }
    public function activity(){ return $this->belongsTo(Activity::class); }
    public function aiScores(){ return $this->hasMany(AiCriterionScore::class); }
}
