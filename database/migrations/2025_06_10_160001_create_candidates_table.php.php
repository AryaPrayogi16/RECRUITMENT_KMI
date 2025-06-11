<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_code', 20)->unique();
            $table->string('position_applied', 100);
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->enum('application_status', ['pending', 'reviewing', 'interview', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes(); 
            
            $table->index(['position_applied']);
            $table->index(['application_status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};