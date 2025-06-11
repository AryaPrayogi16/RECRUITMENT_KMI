<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->string('full_name', 100);
            $table->string('birth_place', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('ethnicity', 50)->nullable();
            $table->enum('marital_status', ['Lajang', 'Menikah', 'Janda', 'Duda'])->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->text('current_address')->nullable();
            $table->text('ktp_address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->enum('residence_status', ['Milik Sendiri', 'Orang Tua', 'Kontrak', 'Sewa'])->nullable();
            $table->integer('height_cm')->nullable();
            $table->integer('weight_kg')->nullable();
            $table->enum('vaccination_status', ['Vaksin 1', 'Vaksin 2', 'Vaksin 3'])->nullable();
            $table->timestamps();
            $table->softDeletes(); 
            
            $table->index(['email']);
            $table->index(['full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_data');
    }
};