<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('members', function (Blueprint $table) {
        $table->id('userId'); // Primary Key
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->text('password');
        $table->text('profile_image')->nullable();
        $table->text('background_image')->nullable();
        $table->boolean('is_admin')->default(false);

        // Stats
        $table->integer('problems_solved')->default(0);
        $table->integer('problem_streak')->default(0);
        $table->integer('problems_created')->default(0);
        $table->string('rank', 50)->nullable();

        // Preferences
        $table->string('preferred_language', 20)->default('javascript');
        $table->integer('editor_font_size')->default(14);
        $table->string('theme', 20)->default('dark');

        $table->softDeletes(); // Replaces "deleted BOOLEAN"
        $table->timestamps(); // Replaces "createdAt"
    });
}

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image', 'deleted', 'is_admin',
                'problems_solved', 'problem_streak', 'problems_created', 'rank',
                'preferred_language', 'editor_font_size', 'theme'
            ]);
        });
    }
};
