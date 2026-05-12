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
    Schema::create('test_cases', function (Blueprint $table) {
        $table->id('testCaseId');
        $table->foreignId('problemId')->constrained('problems', 'problemId')->onDelete('cascade');

        $table->text('input');
        $table->text('expected_output');
        $table->boolean('is_default')->default(false);

        $table->softDeletes();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
