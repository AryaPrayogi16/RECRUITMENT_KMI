<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan nilai 'export' ke ENUM action_type di tabel application_logs
        DB::statement("ALTER TABLE application_logs MODIFY COLUMN action_type ENUM('status_change', 'document_upload', 'data_update', 'export') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke nilai ENUM original
        // PERHATIAN: Rollback akan gagal jika ada data dengan nilai 'export'
        // Pastikan tidak ada data dengan action_type = 'export' sebelum rollback
        DB::statement("ALTER TABLE application_logs MODIFY COLUMN action_type ENUM('status_change', 'document_upload', 'data_update') NOT NULL");
    }
};