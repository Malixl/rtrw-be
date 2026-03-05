<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update the klasifikasi tipe enum to replace 'pkkprl' with 'kawasan_strategi_provinsi'
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','pkkprl','kawasan_strategi_provinsi','data_spasial','batas_administrasi') NOT NULL");

        // Update existing records
        DB::table('klasifikasi')->where('tipe', 'pkkprl')->update(['tipe' => 'kawasan_strategi_provinsi']);

        // Now remove 'pkkprl' from the enum
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','kawasan_strategi_provinsi','data_spasial','batas_administrasi') NOT NULL");
    }

    public function down(): void
    {
        // Re-add 'pkkprl' to enum
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','pkkprl','kawasan_strategi_provinsi','data_spasial','batas_administrasi') NOT NULL");

        // Revert data
        DB::table('klasifikasi')->where('tipe', 'kawasan_strategi_provinsi')->update(['tipe' => 'pkkprl']);

        // Remove 'kawasan_strategi_provinsi' from enum
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','pkkprl','data_spasial','batas_administrasi') NOT NULL");
    }
};
