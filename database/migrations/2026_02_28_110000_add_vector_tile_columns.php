<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tambah kolom vector tile ke semua tabel yang punya geojson_file.
     * - tile_path: path ke file .mbtiles di storage
     * - render_type: 'geojson' (default) atau 'vectortile'
     */
    public function up(): void
    {
        $tables = [
            'polaruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'kawasan_strategi_provinsi',
            'data_spasial',
            'batas_administrasi',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tile_path')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('tile_path')->nullable()->after('geojson_file');
                    $t->string('render_type')->default('geojson')->after('tile_path');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'polaruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'kawasan_strategi_provinsi',
            'data_spasial',
            'batas_administrasi',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn(['tile_path', 'render_type']);
                });
            }
        }
    }
};
