<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['room_id', 'student_id', 'adviser_id'];

    public function room() { return $this->belongsTo(Room::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function adviser() { return $this->belongsTo(User::class, 'adviser_id'); }
    public function messages() { return $this->hasMany(ChatMessage::class, 'conversation_id'); }
    public function latestMessage() { return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany(); }
}
