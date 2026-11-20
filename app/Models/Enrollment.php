<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = ['subject_id','room_id','student_id'];

    public function subject(){ return $this->belongsTo(Subject::class); }
    public function room(){ return $this->belongsTo(Room::class); }
    public function student(){ return $this->belongsTo(User::class,'student_id'); }
}
