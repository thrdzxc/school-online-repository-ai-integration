<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Activity extends Model
{
    /** The same enrollment boundary applies to lists, direct URLs and downloads. */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->whereExists(function ($enrollment) use ($studentId) {
            $enrollment->selectRaw('1')->from('enrollments')
                ->where('enrollments.student_id', $studentId)
                ->whereColumn('enrollments.subject_id', 'activities.subject_id')
                ->where(function ($room) {
                    $room->whereNull('activities.room_id')
                        ->orWhereColumn('enrollments.room_id', 'activities.room_id');
                });
        })->whereHas('subject', fn ($subject) => $subject
            ->whereColumn('subjects.adviser_id', 'activities.adviser_id'));
    }

    protected $fillable = [
        'subject_id','room_id','adviser_id','title','instructions','due_at','max_score','status',
        'allow_text','allow_file','allow_late','accepted_types','rubric_text',
        'instruction_file_path','instruction_original_filename','instruction_mime_type','instruction_file_size',
        'rubric_file_path','rubric_original_filename','rubric_mime_type','rubric_file_size'
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime', 'max_score' => 'decimal:2', 'allow_text' => 'boolean',
            'allow_file' => 'boolean', 'allow_late' => 'boolean'
        ];
    }

    public function subject(){ return $this->belongsTo(Subject::class); }
    public function room(){ return $this->belongsTo(Room::class); }
    public function adviser(){ return $this->belongsTo(User::class,'adviser_id'); }
    public function rubricCriteria(){ return $this->hasMany(RubricCriterion::class)->orderBy('sort_order'); }
    public function submissions(){ return $this->hasMany(Submission::class); }
}
