<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('activities', 'room_id')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->foreignId('room_id')->nullable()->after('subject_id')->constrained('rooms')->nullOnDelete();
                $table->index(['room_id', 'status']);
            });
        }

        // Existing activities are assigned to the first room for their subject.
        // This preserves old data while making section filtering work immediately.
        foreach (DB::table('activities')->whereNull('room_id')->get() as $activity) {
            $roomId = DB::table('rooms')->where('subject_id', $activity->subject_id)->orderBy('id')->value('id');
            if ($roomId) {
                DB::table('activities')->where('id', $activity->id)->update(['room_id' => $roomId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activities', 'room_id')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
                $table->dropIndex(['room_id', 'status']);
                $table->dropColumn('room_id');
            });
        }
    }
};
