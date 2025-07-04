<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driving_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->enum('license_type', ['A', 'B1', 'B2', 'C']);
            $table->timestamps();
            $table->softDeletes(); 
            
            $table->unique(['candidate_id', 'license_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driving_licenses');
    }
};