<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subjects', function(Blueprint $table){
            $table->id();$table->string('code')->unique();$table->string('name');$table->text('description')->nullable();
            $table->foreignId('adviser_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('school_year');$table->string('semester');$table->boolean('active')->default(true);$table->timestamps();
        });
        Schema::create('enrollments', function(Blueprint $table){
            $table->id();$table->foreignId('subject_id')->constrained()->cascadeOnDelete();$table->foreignId('student_id')->constrained('users')->cascadeOnDelete();$table->timestamps();
            $table->unique(['subject_id','student_id']);
        });
        Schema::create('activities', function(Blueprint $table){
            $table->id();$table->foreignId('subject_id')->constrained()->cascadeOnDelete();$table->foreignId('adviser_id')->constrained('users')->restrictOnDelete();
            $table->string('title');$table->longText('instructions')->nullable();$table->dateTime('due_at')->nullable();$table->decimal('max_score',8,2)->default(100);
            $table->enum('status',['draft','published','closed'])->default('draft')->index();
            $table->boolean('allow_text')->default(true);$table->boolean('allow_file')->default(true);$table->boolean('allow_late')->default(true);$table->string('accepted_types')->nullable();$table->timestamps();
        });
        Schema::create('rubric_criteria', function(Blueprint $table){
            $table->id();$table->foreignId('activity_id')->constrained()->cascadeOnDelete();$table->string('name');$table->text('description');$table->decimal('max_points',8,2);$table->unsignedInteger('sort_order')->default(1);$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rubric_criteria');Schema::dropIfExists('activities');Schema::dropIfExists('enrollments');Schema::dropIfExists('subjects'); }
};
