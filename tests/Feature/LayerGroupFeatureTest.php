<?php

namespace Tests\Feature;

use App\Models\DataSpasial;
use App\Models\Klasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LayerGroupFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_layer_group_crud_and_relations()
    {
        // create role & user
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'sanctum');

        // Create LayerGroup via API
        $payload = [
            'nama_layer_group' => 'Peta Dasar',
            'deskripsi' => 'Base layers',
            'urutan_tampil' => 1,
        ];

        $createResp = $this->postJson('/api/layer-groups', $payload);
        $createResp->assertStatus(201);
        $this->assertTrue($createResp->json('status'));

        // Get Layer Groups and pick id
        $listResp = $this->getJson('/api/layer-groups');
        $listResp->assertStatus(200);
        $listResp->assertJson(
            fn (AssertableJson $json) => $json->has('data')->etc()
        );

        $layerGroupId = $listResp->json('data')[0]['id'] ?? null;
        $this->assertNotNull($layerGroupId);

        // Create Klasifikasi with layer_group_id
        $klPayload = [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'layer_group_id' => $layerGroupId,
            'tipe' => 'data_spasial',
        ];

        $klCreate = $this->postJson('/api/klasifikasi', $klPayload);
        $klCreate->assertStatus(201);

        // Ensure klasifikasi has layer_group in GET
        $klList = $this->getJson('/api/klasifikasi');
        $klList->assertStatus(200);
        $klData = $klList->json('data');
        $this->assertNotEmpty($klData);
        $this->assertArrayHasKey('layer_group', $klData[0]);
        $this->assertEquals($layerGroupId, $klData[0]['layer_group']['id']);

        $klId = $klData[0]['id'];

        // Create DataSpasial directly and test nested klasifikasi.layer_group in data_spasial endpoint
        $data = DataSpasial::create([
            'klasifikasi_id' => $klId,
            'nama' => 'Sungai Layer',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'tipe_geometri' => 'polygon',
        ]);

        $dsList = $this->getJson('/api/data_spasial');
        $dsList->assertStatus(200);
        $dsData = $dsList->json('data');
        $this->assertNotEmpty($dsData);
        $this->assertArrayHasKey('klasifikasi', $dsData[0]);
        $this->assertArrayHasKey('layer_group', $dsData[0]['klasifikasi']);

        // Test deleting layer group sets klasifikasi.layer_group to null (onDelete set null)
        $del = $this->deleteJson('/api/layer-groups/'.$layerGroupId);
        $del->assertStatus(200);

        $klListAfter = $this->getJson('/api/klasifikasi');
        $klListAfter->assertStatus(200);
        $klAfterData = $klListAfter->json('data');
        $this->assertNotEmpty($klAfterData);
        $this->assertArrayHasKey('layer_group', $klAfterData[0]);
        $this->assertNull($klAfterData[0]['layer_group']['id']);

        // --- MULTI-DELETE TEST ---
        // Create two new layer groups and klasifikasi linked to them
        $payload1 = ['nama_layer_group' => 'Multi 1', 'deskripsi' => '', 'urutan_tampil' => 10];
        $payload2 = ['nama_layer_group' => 'Multi 2', 'deskripsi' => '', 'urutan_tampil' => 20];

        $this->postJson('/api/layer-groups', $payload1)->assertStatus(201);
        $this->postJson('/api/layer-groups', $payload2)->assertStatus(201);

        $lgList = $this->getJson('/api/layer-groups');
        $lgList->assertStatus(200);
        $lgData = $lgList->json('data');

        // pick the last two created
        $ids = array_slice(array_column($lgData, 'id'), -2);
        $this->assertCount(2, $ids);

        // Create klasifikasi linked to both groups
        foreach ($ids as $idx) {
            $this->postJson('/api/klasifikasi', [
                'nama' => 'KL for '.$idx,
                'deskripsi' => 'desc',

                'layer_group_id' => $idx,
                'tipe' => 'data_spasial',
            ])->assertStatus(201);
        }

        // multi delete via query param
        $multi = $this->deleteJson('/api/layer-groups/multi-delete?ids='.implode(',', $ids));
        $multi->assertStatus(200);

        // verify klasifikasi layer_group becomes null for those klasifikasi
        $klAfter = $this->getJson('/api/klasifikasi');
        $klAfter->assertStatus(200);
        $klAfterArr = $klAfter->json('data');

        // ensure none of klasifikasi have layer_group.id equal to any deleted id
        foreach ($klAfterArr as $item) {
            if (isset($item['layer_group']['id'])) {
                $this->assertNotContains($item['layer_group']['id'], $ids);
            }
        }
    }
}
