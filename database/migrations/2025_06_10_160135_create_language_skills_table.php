<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->enum('language', ['Bahasa Inggris', 'Bahasa Mandarin', 'Lainnya']);
            $table->enum('speaking_level', ['Pemula', 'Menengah', 'Mahir', 'Sangat Mahir'])->nullable();
            $table->enum('writing_level', ['Pemula', 'Menengah', 'Mahir', 'Sangat Mahir'])->nullable();
            $table->string('other_language_name', 50)->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_skills');
    }
};