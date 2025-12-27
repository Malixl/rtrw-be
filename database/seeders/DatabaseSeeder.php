<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(ProfilDesaSeeder::class);
        $this->call(UserSeeder::class);
        // $this->call(KategoriSeeder::class);
        // $this->call(PerangkatDesaSeeder::class);
        // $this->call(PengaturanAplikasiSeeder::class);
    }
}
