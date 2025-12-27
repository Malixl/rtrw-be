<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayerGroupMapFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_layer_groups_with_klasifikasi_grouped_by_type()
    {
        // seed minimal data
        $this->artisan('migrate:fresh --seed');

        // create admin user to setup data via API
        if (! \Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        }
        $user = \App\Models\User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // create layer group
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Test', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);

        $lg = $this->getJson('/api/layer-groups')->json('data')[0];
        $lgId = $lg['id'];

        // create klasifikasi linked to layer group
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Klas A',
            'deskripsi' => 'desc',
            'tipe' => 'pola_ruang',
            'layer_group_id' => $lgId,
        ])->assertStatus(201);

        // create related polaruang entry (assume endpoint and minimal payload exist)
        $kl = $this->getJson('/api/klasifikasi')->json('data')[0];
        $klId = $kl['id'];

        // create asset directly to avoid endpoint validation complexity
        \App\Models\Polaruang::create([
            'klasifikasi_id' => $klId,
            'nama' => 'Polaruang 1',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'warna' => '#000'
        ]);

        // call with-klasifikasi
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi');
        $resp->assertStatus(200);

        $data = $resp->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('klasifikasis', $data[0]);
        $this->assertArrayHasKey('klasifikasi_pola_ruang', $data[0]['klasifikasis']);
        $this->assertNotEmpty($data[0]['klasifikasis']['klasifikasi_pola_ruang']);
    }
}
