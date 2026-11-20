<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RepositoryItem extends Model
{
    protected $fillable = ['user_id','subject_id','title','description','text_content','file_path','original_filename','mime_type','file_size'];
    public function user(){ return $this->belongsTo(User::class); }
    public function subject(){ return $this->belongsTo(Subject::class); }
}
