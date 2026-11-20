<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('class_code', 30)->nullable()->after('id');
            $table->decimal('units', 5, 1)->nullable()->after('name');
            $table->string('schedule', 120)->nullable()->after('units');
            $table->string('room', 80)->nullable()->after('schedule');
            $table->string('instructor_name', 120)->nullable()->after('room');
            $table->string('section', 80)->nullable()->after('instructor_name');
        });

        Schema::table('ai_evaluations', function (Blueprint $table) {
            $table->json('rubric_breakdown')->nullable()->after('recommended_score');
        });
    }

    public function down(): void
    {
        Schema::table('ai_evaluations', function (Blueprint $table) {
            $table->dropColumn('rubric_breakdown');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['class_code','units','schedule','room','instructor_name','section']);
        });
    }
};
