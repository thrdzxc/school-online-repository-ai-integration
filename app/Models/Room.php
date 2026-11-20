<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    public static function newEnrollmentKey(): string
    {
        do {
            $key = 'ROOM-'.strtoupper(Str::random(8));
        } while (static::where('enrollment_key', $key)->exists());

        return $key;
    }

    protected $fillable = [
        'subject_id', 'name', 'section', 'enrollment_key', 'physical_room',
        'schedule', 'capacity', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'capacity' => 'integer'];
    }

    public function subject() { return $this->belongsTo(Subject::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function students() { return $this->belongsToMany(User::class, 'enrollments', 'room_id', 'student_id')->withTimestamps(); }
    public function conversations() { return $this->hasMany(ChatConversation::class); }
}
