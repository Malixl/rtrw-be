<?php

namespace Tests\Feature;

use App\Models\Polaruang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LayerGroupMapFlatFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_flat_format_returns_type_lists()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // create group and klasifikasi, attach children
        $this->postJson('/api/layer-groups', ['nama_layer_group' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');
        $petaDasarId = collect($lgData)->firstWhere('nama_layer_group', 'Peta Dasar')['id'];

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial',
        ])->assertStatus(201);

        $klList = $this->getJson('/api/klasifikasi');
        $klData = $klList->json('data');
        $sungaiId = collect($klData)->firstWhere('nama', 'Sungai')['id'];

        Polaruang::create([
            'klasifikasi_id' => $sungaiId,
            'nama' => 'Sungai Layer',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'warna' => '#000',
        ]);

        // call flat format (no rtrw)
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi?format=flat');
        $resp->assertStatus(200);

        $resp->assertJsonStructure([
            'data' => [
                'klasifikasi_pola_ruang',
                'klasifikasi_struktur_ruang',
                'klasifikasi_ketentuan_khusus',
                'klasifikasi_indikasi_program',
                'klasifikasi_pkkprl',
                'klasifikasi_data_spasial',
            ],
        ]);

        $data = $resp->json('data');

        // ensure pola_ruang contains the Sungai klasifikasi
        $this->assertNotEmpty($data['klasifikasi_pola_ruang']);
        $this->assertEquals('Sungai', $data['klasifikasi_pola_ruang'][0]['nama']);
    }
}
