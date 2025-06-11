<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->enum('education_level', ['SMA/SMK', 'Diploma', 'S1', 'S2', 'S3']);
            $table->string('institution_name', 100)->nullable();
            $table->string('major', 100)->nullable();
            $table->integer('start_month')->nullable();
            $table->integer('start_year')->nullable();
            $table->integer('end_month')->nullable();
            $table->integer('end_year')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->timestamps();
            $table->softDeletes(); 
            
            $table->index(['education_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formal_education');
    }
};