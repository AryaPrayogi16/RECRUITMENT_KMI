<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->string('company_name', 100);
            $table->text('company_address')->nullable();
            $table->string('business_field', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->integer('start_month')->nullable();
            $table->integer('start_year')->nullable();
            $table->integer('end_month')->nullable();
            $table->integer('end_year')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->text('reason_for_leaving')->nullable();
            $table->string('supervisor_name', 100)->nullable();
            $table->string('supervisor_phone', 20)->nullable();
            $table->integer('sequence_order')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
            
            $table->index(['company_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
