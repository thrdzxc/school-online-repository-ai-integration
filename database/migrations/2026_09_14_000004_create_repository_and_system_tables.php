<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('repository_items', function(Blueprint $table){
            $table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();$table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();$table->string('title');$table->text('description')->nullable();$table->longText('text_content')->nullable();$table->string('file_path')->nullable();$table->string('original_filename')->nullable();$table->string('mime_type')->nullable();$table->unsignedBigInteger('file_size')->nullable();$table->timestamps();
        });
        Schema::create('app_notifications', function(Blueprint $table){
            $table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();$table->string('title');$table->text('message');$table->string('url')->nullable();$table->timestamp('read_at')->nullable();$table->timestamps();
        });
        Schema::create('audit_logs', function(Blueprint $table){
            $table->id();$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$table->string('action')->index();$table->string('target_type')->nullable();$table->unsignedBigInteger('target_id')->nullable();$table->json('details')->nullable();$table->string('ip_address',64)->nullable();$table->timestamp('created_at')->useCurrent();
            $table->index(['target_type','target_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs');Schema::dropIfExists('app_notifications');Schema::dropIfExists('repository_items'); }
};
