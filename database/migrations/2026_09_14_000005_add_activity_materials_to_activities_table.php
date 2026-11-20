<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->longText('rubric_text')->nullable()->after('instructions');

            $table->string('instruction_file_path')->nullable()->after('accepted_types');
            $table->string('instruction_original_filename')->nullable()->after('instruction_file_path');
            $table->string('instruction_mime_type')->nullable()->after('instruction_original_filename');
            $table->unsignedBigInteger('instruction_file_size')->nullable()->after('instruction_mime_type');

            $table->string('rubric_file_path')->nullable()->after('instruction_file_size');
            $table->string('rubric_original_filename')->nullable()->after('rubric_file_path');
            $table->string('rubric_mime_type')->nullable()->after('rubric_original_filename');
            $table->unsignedBigInteger('rubric_file_size')->nullable()->after('rubric_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'rubric_text',
                'instruction_file_path',
                'instruction_original_filename',
                'instruction_mime_type',
                'instruction_file_size',
                'rubric_file_path',
                'rubric_original_filename',
                'rubric_mime_type',
                'rubric_file_size',
            ]);
        });
    }
};
