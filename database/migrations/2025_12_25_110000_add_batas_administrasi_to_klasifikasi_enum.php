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
        // Modify the enum to add 'batas_administrasi' only for DB engines that support enum modifications.
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'])) {
            // include 'data_spasial' as it was in the original create migration
            DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','pkkprl','data_spasial','batas_administrasi') NOT NULL");
        }
        // sqlite (used in tests) does not support modifying enum - skip.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'])) {
            // restore previous enum set (include data_spasial)
            DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','pkkprl','data_spasial') NOT NULL");
        }
    }
};
