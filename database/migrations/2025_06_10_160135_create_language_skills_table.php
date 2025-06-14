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
        Schema::create('language_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('language')->nullable();
            $table->enum('speaking_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->enum('writing_level', ['Pemula', 'Menengah', 'Mahir'])->nullable();
            $table->timestamps();
            $table->softDeletes();     
            $table->index('candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_skills');
    }
};