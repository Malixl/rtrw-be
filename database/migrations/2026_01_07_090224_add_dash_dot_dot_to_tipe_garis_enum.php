<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Untuk MySQL, kita perlu mengubah kolom enum dengan raw SQL
        DB::statement("ALTER TABLE data_spasial MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold', 'dash-dot-dot') NULL");
        DB::statement("ALTER TABLE struktur_ruang MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold', 'dash-dot-dot') NULL");
        DB::statement("ALTER TABLE ketentuan_khusus MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold', 'dash-dot-dot') NULL");
        DB::statement("ALTER TABLE pkkprl MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold', 'dash-dot-dot') NULL");
        DB::statement("ALTER TABLE batas_administrasi MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold', 'dash-dot-dot') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke enum lama
        DB::statement("ALTER TABLE data_spasial MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold') NULL");
        DB::statement("ALTER TABLE struktur_ruang MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold') NULL");
        DB::statement("ALTER TABLE ketentuan_khusus MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold') NULL");
        DB::statement("ALTER TABLE pkkprl MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold') NULL");
        DB::statement("ALTER TABLE batas_administrasi MODIFY COLUMN tipe_garis ENUM('solid', 'dashed', 'bold') NULL");
    }
};
