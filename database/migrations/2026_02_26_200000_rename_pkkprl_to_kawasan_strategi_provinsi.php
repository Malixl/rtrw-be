<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::rename('pkkprl', 'kawasan_strategi_provinsi');
    }

    public function down(): void
    {
        Schema::rename('kawasan_strategi_provinsi', 'pkkprl');
    }
};
