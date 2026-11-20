<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'activity_id','student_id','version','text_content','file_path','original_filename','mime_type',
        'file_size','status','is_late','submitted_at'
    ];
    protected function casts(): array { return ['submitted_at'=>'datetime','is_late'=>'boolean']; }

    public function activity(){ return $this->belongsTo(Activity::class); }
    public function student(){ return $this->belongsTo(User::class,'student_id'); }
    public function aiEvaluations(){ return $this->hasMany(AiEvaluation::class)->latest(); }
    public function latestAiEvaluation(){ return $this->hasOne(AiEvaluation::class)->where('status','completed')->latestOfMany(); }
    public function grade(){ return $this->hasOne(Grade::class); }
}
