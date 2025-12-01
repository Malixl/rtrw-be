<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batas_administrasi', function (Blueprint $table) {
            $table->string('warna')->nullable()->default('#000000')->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('batas_administrasi', function (Blueprint $table) {
            $table->dropColumn('warna');
        });
    }
};
