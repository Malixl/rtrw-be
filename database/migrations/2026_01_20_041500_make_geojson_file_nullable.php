<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Allow geojson_file to be NULL so queue processing can set it later
     */
    public function up(): void
    {
        $tables = [
            'data_spasial',
            'batas_administrasi', 
            'polaruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'pkkprl'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'geojson_file')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('geojson_file')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversing as it could cause data loss
    }
};
