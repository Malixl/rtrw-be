<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Remove rtrw_id column on klasifikasi if exists (drop FK if present)
        if (Schema::hasTable('klasifikasi') && Schema::hasColumn('klasifikasi', 'rtrw_id')) {
            // check information_schema for foreign key existence
            $constraint = null;
            try {
                $rows = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'klasifikasi' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'klasifikasi_rtrw_id_foreign'");
                $constraint = ! empty($rows) ? $rows[0]->CONSTRAINT_NAME : null;
            } catch (\Throwable $e) {
                $constraint = null;
            }

            Schema::table('klasifikasi', function (Blueprint $table) use ($constraint) {
                if ($constraint) {
                    try {
                        $table->dropForeign(['rtrw_id']);
                    } catch (\Throwable $e) {
                        // ignore if drop fails
                    }
                }

                if (Schema::hasColumn('klasifikasi', 'rtrw_id')) {
                    $table->dropColumn('rtrw_id');
                }
            });
        }

        // 2) Drop rtrw and periode tables if they exist (non-destructive if already missing)
        if (Schema::hasTable('rtrw')) {
            Schema::dropIfExists('rtrw');
        }

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

        // Re-add rtrw_id to klasifikasi if the table exists and column missing
        if (Schema::hasTable('klasifikasi') && ! Schema::hasColumn('klasifikasi', 'rtrw_id')) {
            Schema::table('klasifikasi', function (Blueprint $table) {
                $table->foreignId('rtrw_id')->constrained('rtrw')->onDelete('cascade');
            });
        }
    }
};
