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
        Schema::create('klasifikasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layer_group_id')->nullable();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['struktur_ruang', 'pola_ruang', 'ketentuan_khusus', 'indikasi_program', 'pkkprl', 'data_spasial']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasi');
    }
};
