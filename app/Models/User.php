<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'school_id',
        'course',
        'year_level',
        'section',
        'department',
        'active',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // Existing Sanctum/API functionality is kept. These relationships support
    // the School Online Repository modules added to the same Laravel project.
    public function advisedSubjects()
    {
        return $this->hasMany(Subject::class, 'adviser_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'enrollments', 'student_id', 'subject_id')->withTimestamps();
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'enrollments', 'student_id', 'room_id')->withTimestamps();
    }

    public function studentConversations()
    {
        return $this->hasMany(ChatConversation::class, 'student_id');
    }

    public function adviserConversations()
    {
        return $this->hasMany(ChatConversation::class, 'adviser_id');
    }

    public function repositoryItems()
    {
        return $this->hasMany(RepositoryItem::class);
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? asset($this->profile_photo_path) : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdviser(): bool
    {
        return $this->role === 'adviser';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
