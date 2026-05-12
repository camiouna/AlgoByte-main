<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('like_activities', function (Blueprint $table) {
    $table->id('likeId');
    $table->foreignId('userId')->constrained('members', 'userId')->onDelete('cascade');
    $table->foreignId('sharedSolutionId')->nullable()->constrained('shared_solutions', 'solutionId')->onDelete('cascade');
    $table->foreignId('discussionId')->nullable()->constrained('discussions', 'discussionId')->onDelete('cascade');
    $table->foreignId('commentId')->nullable()->constrained('comments', 'commentId')->onDelete('cascade');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('like_activities');
    }
};
