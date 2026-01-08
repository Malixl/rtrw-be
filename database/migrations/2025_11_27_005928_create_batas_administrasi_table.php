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
        Schema::create('batas_administrasi', function (Blueprint $table) {
            $table->id();
            // relation to klasifikasi (nullable for safe transition)
            $table->foreignId('klasifikasi_id')->nullable()->constrained('klasifikasi')->nullOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('geojson_file');
            $table->enum('tipe_geometri', ['polyline', 'polygon']);
            $table->enum('tipe_garis', ['solid', 'dashed', 'bold', 'dash-dot-dot'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batas_administrasi', function (Blueprint $table) {
            if (Schema::hasColumn('batas_administrasi', 'klasifikasi_id')) {
                $table->dropForeign(['klasifikasi_id']);
                $table->dropColumn('klasifikasi_id');
            }
        });

        Schema::dropIfExists('batas_administrasi');
    }
};
