<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('school_id', 60)->nullable()->unique();
            $table->string('role', 20)->default('student')->index();
            $table->string('course', 100)->nullable();
            $table->string('year_level', 30)->nullable();
            $table->string('section', 30)->nullable();
            $table->string('department', 100)->nullable();
            $table->boolean('active')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_school_id_unique');
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_active_index');
            $table->dropColumn([
                'school_id', 'role', 'course', 'year_level',
                'section', 'department', 'active',
            ]);
        });
    }
};
