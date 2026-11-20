<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('section')->nullable();
            $table->string('enrollment_key', 40)->unique();
            $table->string('physical_room')->nullable();
            $table->string('schedule')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('subject_id')->constrained('rooms')->nullOnDelete();
        });

        // Convert the existing subject-based enrollment data into one organized
        // room per subject. This keeps all current students enrolled after update.
        foreach (DB::table('subjects')->orderBy('id')->get() as $subject) {
            $class = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) ($subject->class_code ?: $subject->code)));
            $section = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) ($subject->section ?: 'ROOM')));
            $baseKey = substr(($class ?: 'CLASS').'-'.($section ?: 'ROOM').'-'.str_pad((string) $subject->id, 3, '0', STR_PAD_LEFT), 0, 40);
            $key = $baseKey;
            $suffix = 1;
            while (DB::table('rooms')->where('enrollment_key', $key)->exists()) {
                $key = substr($baseKey, 0, 35).'-'.$suffix++;
            }

            $roomId = DB::table('rooms')->insertGetId([
                'subject_id' => $subject->id,
                'name' => trim(($subject->class_code ? $subject->class_code.' · ' : '').$subject->code),
                'section' => $subject->section,
                'enrollment_key' => $key,
                'physical_room' => $subject->room,
                'schedule' => $subject->schedule,
                'capacity' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('enrollments')->where('subject_id', $subject->id)->update(['room_id' => $roomId]);
        }
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
        Schema::dropIfExists('rooms');
    }
};
