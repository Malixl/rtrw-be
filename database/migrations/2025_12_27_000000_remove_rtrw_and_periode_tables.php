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
        // Remove foreign key and column from klasifikasi first
        if (Schema::hasTable('klasifikasi')) {
            Schema::table('klasifikasi', function (Blueprint $table) {
                if (Schema::hasColumn('klasifikasi', 'rtrw_id')) {
                    // drop FK then column
                    $table->dropForeign(['rtrw_id']);
                    $table->dropColumn('rtrw_id');
                }
            });
        }

        // Drop rtrw table
        if (Schema::hasTable('rtrw')) {
            Schema::dropIfExists('rtrw');
        }

        // Drop periode table
        if (Schema::hasTable('periode')) {
            Schema::dropIfExists('periode');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate periode
        if (! Schema::hasTable('periode')) {
            Schema::create('periode', function (Blueprint $table) {
                $table->id();
                $table->year('tahun_mulai');
                $table->year('tahun_akhir');
                $table->timestamps();
            });
        }

        // Recreate rtrw with periode FK
        if (! Schema::hasTable('rtrw')) {
            Schema::create('rtrw', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->text('deskripsi')->nullable();
                $table->foreignId('periode_id')->constrained('periode')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // Re-add rtrw_id to klasifikasi
        if (Schema::hasTable('klasifikasi') && ! Schema::hasColumn('klasifikasi', 'rtrw_id')) {
            Schema::table('klasifikasi', function (Blueprint $table) {
                $table->foreignId('rtrw_id')->constrained('rtrw')->onDelete('cascade');
            });
        }
    }
};
