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
        // Add processing_status to all tables that handle GeoJSON files
        $tables = [
            'data_spasial',
            'batas_administrasi', 
            'polaruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'pkkprl'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'processing_status')) {
                        $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])
                            ->default('completed')
                            ->after('geojson_file');
                    }
                    if (!Schema::hasColumn($tableName, 'processing_error')) {
                        $table->text('processing_error')->nullable()->after('processing_status');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'processing_status')) {
                        $table->dropColumn('processing_status');
                    }
                    if (Schema::hasColumn($tableName, 'processing_error')) {
                        $table->dropColumn('processing_error');
                    }
                });
            }
        }
    }
};
