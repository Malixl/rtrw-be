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
        Schema::create('pkkprl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi')->onDelete('cascade');
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('geojson_file');
            $table->string('warna');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkkprl');
    }
};
