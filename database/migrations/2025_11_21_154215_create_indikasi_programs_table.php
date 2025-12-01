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
        Schema::create('indikasi_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi')->onDelete('cascade');
            $table->string('nama');
            $table->string('file_dokumen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikasi_program');
    }
};
