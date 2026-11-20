<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model
{
    protected $fillable = ['submission_id','ai_score','adviser_score','final_score','decision','admin_override','adviser_comment','finalized_by','finalized_at'];
    protected function casts(): array { return ['ai_score'=>'decimal:2','adviser_score'=>'decimal:2','final_score'=>'decimal:2','admin_override'=>'boolean','finalized_at'=>'datetime']; }
    public function submission(){ return $this->belongsTo(Submission::class); }
    public function finalizedBy(){ return $this->belongsTo(User::class,'finalized_by'); }
}
