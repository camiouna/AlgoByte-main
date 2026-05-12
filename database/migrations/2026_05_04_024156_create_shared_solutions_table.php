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
    Schema::create('shared_solutions', function (Blueprint $table) {
        $table->id('solutionId');
        $table->foreignId('userId')->constrained('members', 'userId')->onDelete('cascade');
        $table->foreignId('problemId')->constrained('problems', 'problemId')->onDelete('cascade');
        $table->foreignId('submissionId')->nullable()->constrained('submissions', 'submissionId')->onDelete('set null');

        $table->string('title');
        $table->text('code');
        $table->text('explanation')->nullable();

        $table->softDeletes(); // Replaces "deleted BOOLEAN"
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_solutions');
    }
};
