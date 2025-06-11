<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->boolean('willing_to_travel')->default(false);
            $table->boolean('has_vehicle')->default(false);
            $table->string('vehicle_types', 100)->nullable();
            $table->text('motivation')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('strengths')->nullable();
            $table->text('other_income_sources')->nullable();
            $table->text('criminal_record')->nullable();
            $table->text('medical_history')->nullable();
            $table->boolean('has_tattoo_piercing')->default(false);
            $table->text('other_company_ownership')->nullable();
            $table->integer('annual_sick_days')->nullable();
            $table->date('start_work_date')->nullable();
            $table->string('information_source', 100)->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_information');
    }
};
