<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\Periode;
use App\Models\Polaruang;
use App\Models\Rtrw;
use App\Models\StrukturRuang;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class LayerGroupMapFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_layer_groups_with_klasifikasi_and_geo_children()
    {
        // create admin user to setup data via API
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // Create periode and rtrw
        $periodeId = \Illuminate\Support\Facades\DB::table('periode')->insertGetId([
            'tahun_mulai' => 2020,
            'tahun_akhir' => 2025,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rtrwId = \Illuminate\Support\Facades\DB::table('rtrw')->insertGetId([
            'nama' => 'RTRW Map Test',
            'deskripsi' => 'desc',
            'periode_id' => $periodeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create two layer groups
        $this->postJson('/api/layer-groups', ['nama_layer_group' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);
        $this->postJson('/api/layer-groups', ['nama_layer_group' => 'Peta Tematik', 'deskripsi' => '', 'urutan_tampil' => 2])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');

        $this->assertCount(2, $lgData);

        $petaDasarId = collect($lgData)->firstWhere('nama_layer_group', 'Peta Dasar')['id'];
        $petaTematikId = collect($lgData)->firstWhere('nama_layer_group', 'Peta Tematik')['id'];

        // create klasifikasi for each group
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'rtrw_id' => $rtrwId,
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial'
        ])->assertStatus(201);

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Toponym',
            'deskripsi' => 'toponym layer',
            'rtrw_id' => $rtrwId,
            'layer_group_id' => $petaTematikId,
            'tipe' => 'struktur_ruang'
        ])->assertStatus(201);

        // fetch klasifikasi ids
        $klList = $this->getJson('/api/klasifikasi');
        $klData = $klList->json('data');
        $sungaiId = collect($klData)->firstWhere('nama', 'Sungai')['id'];
        $topoId = collect($klData)->firstWhere('nama', 'Toponym')['id'];

        // attach geo children
        Polaruang::create([
            'klasifikasi_id' => $sungaiId,
            'nama' => 'Sungai Layer',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'warna' => '#000'
        ]);

        StrukturRuang::create([
            'klasifikasi_id' => $topoId,
            'nama' => 'Topography',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'tipe_geometri' => 'polyline',
            'icon_titik' => null,
            'tipe_garis' => 'solid',
            'warna' => '#111'
        ]);

        // Now call the new endpoint
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi?rtrw_id=' . $rtrwId);
        $resp->assertStatus(200);

        $resp->assertJson(
            fn(\Illuminate\Testing\Fluent\AssertableJson $json) =>
            $json->has('data')
                ->etc()
        );

        $data = $resp->json('data');

        // ensure groups contain klasifikasis with nested children
        $this->assertNotEmpty($data);

        // Find Peta Dasar and check Sungai klasifikasi has pola_ruang
        $petaDasar = collect($data)->firstWhere('nama_layer_group', 'Peta Dasar');
        $this->assertNotNull($petaDasar);
        $this->assertNotEmpty($petaDasar['klasifikasis']);
        $sungai = collect($petaDasar['klasifikasis'])->firstWhere('nama', 'Sungai');
        $this->assertNotNull($sungai);
        $this->assertArrayHasKey('pola_ruang', $sungai);
        $this->assertNotEmpty($sungai['pola_ruang']);

        // Find Peta Tematik and check Toponym has struktur_ruang
        $petaTematik = collect($data)->firstWhere('nama_layer_group', 'Peta Tematik');
        $this->assertNotNull($petaTematik);
        $topo = collect($petaTematik['klasifikasis'])->firstWhere('nama', 'Toponym');
        $this->assertNotNull($topo);
        $this->assertArrayHasKey('struktur_ruang', $topo);
        $this->assertNotEmpty($topo['struktur_ruang']);
    }

    public function test_default_compact_hides_empty_relations()
    {
        // create admin user to setup data via API
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // Create periode and rtrw
        $periodeId = \Illuminate\Support\Facades\DB::table('periode')->insertGetId([
            'tahun_mulai' => 2020,
            'tahun_akhir' => 2025,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rtrwId = \Illuminate\Support\Facades\DB::table('rtrw')->insertGetId([
            'nama' => 'RTRW Compact Test',
            'deskripsi' => 'desc',
            'periode_id' => $periodeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create one layer group
        $this->postJson('/api/layer-groups', ['nama_layer_group' => 'Peta Compact', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');
        $petaCompactId = collect($lgData)->firstWhere('nama_layer_group', 'Peta Compact')['id'];

        // create klasifikasi with only data_spasial child
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Perahu',
            'deskripsi' => 'layer perahu',
            'rtrw_id' => $rtrwId,
            'layer_group_id' => $petaCompactId,
            'tipe' => 'data_spasial'
        ])->assertStatus(201);

        $klList = $this->getJson('/api/klasifikasi');
        $klData = $klList->json('data');
        $perahuId = collect($klData)->firstWhere('nama', 'Perahu')['id'];

        // attach data_spasial only
        \App\Models\DataSpasial::create([
            'klasifikasi_id' => $perahuId,
            'nama' => 'Danau',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'tipe_geometri' => 'polygon'
        ]);

        // Now call the new endpoint without compact param (default compact=true)
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi?rtrw_id=' . $rtrwId);
        $resp->assertStatus(200);

        $data = $resp->json('data');
        $this->assertNotEmpty($data);

        $petaCompact = collect($data)->firstWhere('nama_layer_group', 'Peta Compact');
        $this->assertNotNull($petaCompact);
        $k = collect($petaCompact['klasifikasis'])->firstWhere('nama', 'Perahu');
        $this->assertNotNull($k);

        // default compact should NOT include empty relation keys
        $this->assertArrayNotHasKey('pola_ruang', $k);
        $this->assertArrayNotHasKey('struktur_ruang', $k);
        $this->assertArrayNotHasKey('ketentuan_khusus', $k);
        $this->assertArrayNotHasKey('indikasi_program', $k);
        $this->assertArrayNotHasKey('pkkprl', $k);

        // but it should include data_spasial
        $this->assertArrayHasKey('data_spasial', $k);
        $this->assertNotEmpty($k['data_spasial']);
    }
}
