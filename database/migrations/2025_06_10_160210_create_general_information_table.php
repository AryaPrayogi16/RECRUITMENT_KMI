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
        Schema::create('general_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained('candidates')->onDelete('cascade');
            $table->boolean('willing_to_travel')->default(false);
            $table->boolean('has_vehicle')->default(false);
            $table->string('vehicle_types')->nullable();
            $table->text('motivation')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->string('other_income')->nullable();
            $table->boolean('has_police_record')->default(false);
            $table->string('police_record_detail')->nullable();
            $table->boolean('has_serious_illness')->default(false);
            $table->string('illness_detail')->nullable();
            $table->boolean('has_tattoo_piercing')->default(false);
            $table->string('tattoo_piercing_detail')->nullable();
            $table->boolean('has_other_business')->default(false);
            $table->string('other_business_detail')->nullable();
            $table->integer('absence_days')->nullable();
            $table->date('start_work_date')->nullable();
            $table->string('information_source')->nullable();
            $table->boolean('agreement')->default(false);
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
        Schema::dropIfExists('general_information');
    }
};