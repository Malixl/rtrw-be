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
        // Guard: buat hanya jika tabel belum ada (menghindari fatal error bila tabel dibuat manual/terlebih dahulu)
        if (! Schema::hasTable('data_spasial')) {
            Schema::create('data_spasial', function (Blueprint $table) {
                $table->id();
                $table->foreignId('klasifikasi_id')->constrained('klasifikasi')->onDelete('cascade');
                $table->string('nama');
                $table->text('deskripsi')->nullable();
                $table->string('geojson_file');
                $table->enum('tipe_geometri', ['polyline', 'point', 'polygon']);
                $table->string('icon_titik')->nullable();
                $table->enum('tipe_garis', ['solid', 'dashed', 'bold'])->nullable();
                $table->string('warna')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_spasial');
    }
};
