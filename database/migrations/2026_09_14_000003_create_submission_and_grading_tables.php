<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('submissions', function(Blueprint $table){
            $table->id();$table->foreignId('activity_id')->constrained()->cascadeOnDelete();$table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);$table->longText('text_content')->nullable();$table->string('file_path')->nullable();$table->string('original_filename')->nullable();$table->string('mime_type')->nullable();$table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status',['submitted','ai_reviewed','ai_failed','returned','graded','superseded'])->default('submitted')->index();$table->boolean('is_late')->default(false);$table->dateTime('submitted_at');$table->timestamps();
            $table->unique(['activity_id','student_id','version']);
        });
        Schema::create('ai_evaluations', function(Blueprint $table){
            $table->id();$table->foreignId('submission_id')->constrained()->cascadeOnDelete();$table->string('provider')->nullable();$table->string('model')->nullable();$table->decimal('recommended_score',8,2)->nullable();
            $table->longText('summary')->nullable();$table->json('strengths')->nullable();$table->json('improvements')->nullable();$table->decimal('confidence',4,2)->nullable();$table->json('raw_response')->nullable();
            $table->enum('status',['completed','failed'])->default('completed')->index();$table->text('error_message')->nullable();$table->boolean('is_demo')->default(false);$table->dateTime('evaluated_at')->nullable();$table->timestamps();
        });
        Schema::create('ai_criterion_scores', function(Blueprint $table){
            $table->id();$table->foreignId('ai_evaluation_id')->constrained()->cascadeOnDelete();$table->foreignId('rubric_criterion_id')->constrained()->cascadeOnDelete();$table->decimal('score',8,2);$table->text('feedback')->nullable();$table->timestamps();
        });
        Schema::create('grades', function(Blueprint $table){
            $table->id();$table->foreignId('submission_id')->unique()->constrained()->cascadeOnDelete();$table->decimal('ai_score',8,2)->nullable();$table->decimal('adviser_score',8,2)->nullable();$table->decimal('final_score',8,2);$table->enum('decision',['approved','modified'])->default('approved');$table->text('adviser_comment')->nullable();$table->foreignId('finalized_by')->constrained('users')->restrictOnDelete();$table->dateTime('finalized_at');$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('grades');Schema::dropIfExists('ai_criterion_scores');Schema::dropIfExists('ai_evaluations');Schema::dropIfExists('submissions'); }
};
