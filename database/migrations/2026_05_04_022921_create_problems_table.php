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
    Schema::create('problems', function (Blueprint $table) {
        $table->id('problemId');
        // Foreign Key Relationship
        $table->foreignId('creatorId')->constrained('members', 'userId');

        $table->string('title');
        $table->text('description')->nullable();
        $table->string('difficulty', 20);
        $table->string('status', 20)->default('draft');
        $table->string('visibility', 20)->default('public');
        $table->text('solution')->nullable();
        $table->text('explanation')->nullable();

        $table->softDeletes();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
