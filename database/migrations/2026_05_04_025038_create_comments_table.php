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
        Schema::create('comments', function (Blueprint $table) {
    $table->id('commentId');
    $table->foreignId('userId')->constrained('members', 'userId')->onDelete('cascade');
    $table->foreignId('sharedSolutionId')->nullable()->constrained('shared_solutions', 'solutionId')->onDelete('cascade');
    $table->foreignId('discussionId')->nullable()->constrained('discussions', 'discussionId')->onDelete('cascade');
    $table->text('content');
    $table->softDeletes();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
