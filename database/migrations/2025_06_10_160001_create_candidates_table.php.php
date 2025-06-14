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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_code')->unique();
            $table->foreignId('position_id')->constrained('positions');
            $table->string('position_applied'); // Store actual position name for history
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('application_status', [
                'draft',
                'submitted',
                'screening',
                'interview',
                'offered',
                'accepted',
                'rejected'
            ])->default('submitted');
            $table->date('application_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('candidate_code');
            $table->index('application_status');
            $table->index('position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};