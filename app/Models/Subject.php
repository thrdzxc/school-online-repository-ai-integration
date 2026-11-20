<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'class_code','name','description','units','schedule','room','instructor_name','section',
        'adviser_id','school_year','semester','active'
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'units' => 'decimal:1'];
    }

    public function adviser() { return $this->belongsTo(User::class, 'adviser_id'); }
    public function students() { return $this->belongsToMany(User::class, 'enrollments', 'subject_id', 'student_id')->withTimestamps(); }
    public function activities() { return $this->hasMany(Activity::class); }
    public function rooms() { return $this->hasMany(Room::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }


    public function shortLabel(): string
    {
        return implode(' · ', array_filter([$this->name, $this->section, $this->school_year, $this->semester]));
    }

    public function displayLabel(): string
    {
        $parts = [];
        if ($this->class_code) $parts[] = $this->class_code;
        $parts[] = $this->name;
        if ($this->section) $parts[] = $this->section;
        if ($this->schedule) $parts[] = $this->schedule;
        return implode(' · ', $parts);
    }
}
