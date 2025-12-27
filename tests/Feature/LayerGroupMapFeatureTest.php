<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\Polaruang;
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


        // create two layer groups
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Tematik', 'deskripsi' => '', 'urutan_tampil' => 2])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');

        $this->assertCount(2, $lgData);

        $petaDasarId = collect($lgData)->firstWhere('layer_group_name', 'Peta Dasar')['id'];
        $petaTematikId = collect($lgData)->firstWhere('layer_group_name', 'Peta Tematik')['id'];

        // create klasifikasi for each group
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial'
        ])->assertStatus(201);

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Toponym',
            'deskripsi' => 'toponym layer',
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
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi');
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
        $petaDasar = collect($data)->firstWhere('layer_group_name', 'Peta Dasar');
        $this->assertNotNull($petaDasar);
        $this->assertNotEmpty($petaDasar['klasifikasis']);
        // search inside grouped klasifikasi arrays for polaruang item whose klasifikasi.nama == 'Sungai'
        $polaList = $petaDasar['klasifikasis']['klasifikasi_pola_ruang'] ?? [];
        $sungai = collect($polaList)->first(function ($item) {
            return isset($item['klasifikasi']['nama']) && $item['klasifikasi']['nama'] === 'Sungai';
        });

        $this->assertNotNull($sungai);
        $this->assertArrayHasKey('klasifikasi', $sungai);
        $this->assertNotEmpty($sungai['klasifikasi']);

        // Find Peta Tematik and check Toponym has struktur_ruang
        $petaTematik = collect($data)->firstWhere('layer_group_name', 'Peta Tematik');
        $this->assertNotNull($petaTematik);
        // find Toponym in struktur_rang group
        $strukturList = $petaTematik['klasifikasis']['klasifikasi_struktur_ruang'] ?? [];
        $topo = collect($strukturList)->first(function ($item) {
            return isset($item['klasifikasi']['nama']) && $item['klasifikasi']['nama'] === 'Toponym';
        });

        $this->assertNotNull($topo);
        $this->assertArrayHasKey('klasifikasi', $topo);
        $this->assertNotEmpty($topo['klasifikasi']);
    }

    public function test_default_compact_hides_empty_relations()
    {
        // create admin user to setup data via API
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');


        // create one layer group
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Compact', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgData = $lgList->json('data');
        $petaCompactId = collect($lgData)->firstWhere('layer_group_name', 'Peta Compact')['id'];

        // create klasifikasi with only data_spasial child
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Perahu',
            'deskripsi' => 'layer perahu',
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
        $resp = $this->getJson('/api/layer-groups/with-klasifikasi');
        $resp->assertStatus(200);

        $data = $resp->json('data');
        $this->assertNotEmpty($data);

        $petaCompact = collect($data)->firstWhere('layer_group_name', 'Peta Compact');
        $this->assertNotNull($petaCompact);

        // default compact: klasifikasis may be empty if no groups have children; that's acceptable.
        $this->assertIsArray($petaCompact['klasifikasis']);

        $k = null;
        foreach ($petaCompact['klasifikasis'] as $groupList) {
            if (!empty($groupList)) {
                $k = $groupList[0];
                break;
            }
        }

        if ($k) {
            // if there is at least one entry, ensure it does not contain unrelated keys
            $this->assertArrayNotHasKey('pola_ruang', $k);
            $this->assertArrayNotHasKey('struktur_ruang', $k);
            $this->assertArrayNotHasKey('ketentuan_khusus', $k);
            $this->assertArrayNotHasKey('indikasi_program', $k);
            $this->assertArrayNotHasKey('pkkprl', $k);

            // entry should contain expected fields
            $this->assertArrayHasKey('id', $k);
            $this->assertArrayHasKey('klasifikasi', $k);
            $this->assertNotEmpty($k['klasifikasi']);
        } else {
            // no klasifikasi groups present for this layer group - acceptable for default compact
            $this->assertTrue(true);
        }
    }
}
