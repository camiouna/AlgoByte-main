<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussions', function (Blueprint $table) {
    $table->id('discussionId');
    $table->foreignId('userId')->constrained('members', 'userId')->onDelete('cascade');
    $table->foreignId('problemId')->constrained('problems', 'problemId')->onDelete('cascade');
    $table->string('title');
    $table->text('content');
    $table->integer('upvotes')->default(0);
    $table->softDeletes();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('discussions');
    }
};
