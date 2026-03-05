<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename table indikasi_program → dokumen
        Schema::rename('indikasi_program', 'dokumen');

        // 2. Update klasifikasi.tipe ENUM: add 'dokumen', keep old values for migration
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','kawasan_strategi_provinsi','data_spasial','batas_administrasi','dokumen') NOT NULL");

        // 3. Migrate existing data
        DB::table('klasifikasi')->where('tipe', 'indikasi_program')->update(['tipe' => 'dokumen']);

        // 4. Remove old ENUM value
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','kawasan_strategi_provinsi','data_spasial','batas_administrasi','dokumen') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add old ENUM value
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','kawasan_strategi_provinsi','data_spasial','batas_administrasi','dokumen') NOT NULL");

        // 2. Migrate data back
        DB::table('klasifikasi')->where('tipe', 'dokumen')->update(['tipe' => 'indikasi_program']);

        // 3. Remove new ENUM value
        DB::statement("ALTER TABLE `klasifikasi` MODIFY `tipe` ENUM('struktur_ruang','pola_ruang','ketentuan_khusus','indikasi_program','kawasan_strategi_provinsi','data_spasial','batas_administrasi') NOT NULL");

        // 4. Rename table back
        Schema::rename('dokumen', 'indikasi_program');
    }
};
