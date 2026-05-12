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
    Schema::create('submissions', function (Blueprint $table) {
    $table->id('submissionId');
    // Relationships
    $table->foreignId('userId')->constrained('members', 'userId')->onDelete('cascade');
    $table->foreignId('problemId')->constrained('problems', 'problemId')->onDelete('cascade');

    // Core Data
    $table->text('code');
    $table->string('language', 50);
    $table->string('status', 50)->default('received');

    // Execution Metadata (Merged from code_submissions)
    $table->longText('stdout')->nullable();
    $table->longText('stderr')->nullable();
    $table->longText('compile_output')->nullable();
    $table->integer('exit_code')->nullable();
    $table->unsignedInteger('execution_time_ms')->nullable();
    $table->string('runtime')->nullable();
    $table->string('runtime_version')->nullable();
    $table->string('signal')->nullable();

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
