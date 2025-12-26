<?php

namespace Tests\Feature;

use App\Models\Polaruang;
use App\Models\StrukturRuang;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class LayerGroupMapFlatFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_flat_format_returns_type_lists()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // setup periode and rtrw
        $periodeId = \Illuminate\Support\Facades\DB::table('periode')->insertGetId([
            'tahun_mulai' => 2020,
            'tahun_akhir' => 2025,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rtrwId = \Illuminate\Support\Facades\DB::table('rtrw')->insertGetId([
            'nama' => 'RTRW Flat Test',
            'deskripsi' => 'desc',
            'periode_id' => $periodeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create group and klasifikasi, attach children
        $this->postJson('/api/layer-groups', ['nama_layer_group' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');
        $petaDasarId = collect($lgData)->firstWhere('nama_layer_group', 'Peta Dasar')['id'];

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'rtrw_id' => $rtrwId,
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial'
        ])->assertStatus(201);

        $klList = $this->getJson('/api/klasifikasi');
        $klData = $klList->json('data');
        $sungaiId = collect($klData)->firstWhere('nama', 'Sungai')['id'];

        Polaruang::create([
            'klasifikasi_id' => $sungaiId,
            'nama' => 'Sungai Layer',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'warna' => '#000'
        ]);

        // call flat format
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi?format=flat&rtrw_id=' . $rtrwId);
        $resp->assertStatus(200);

        $resp->assertJsonStructure([
            'data' => [
                'rtrw',
                'klasifikasi_pola_ruang',
                'klasifikasi_struktur_ruang',
                'klasifikasi_ketentuan_khusus',
                'klasifikasi_indikasi_program',
                'klasifikasi_pkkprl',
                'klasifikasi_data_spasial',
            ]
        ]);

        $data = $resp->json('data');

        // ensure pola_ruang contains the Sungai klasifikasi
        $this->assertNotEmpty($data['klasifikasi_pola_ruang']);
        $this->assertEquals('Sungai', $data['klasifikasi_pola_ruang'][0]['nama']);
    }
}
