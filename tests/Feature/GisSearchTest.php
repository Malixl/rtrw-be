<?php

namespace Tests\Feature;

use App\Models\Klasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GisSearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_search_by_klasifikasi_id_returns_klasifikasi_with_children()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // create layer group
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);
        $lg = $this->getJson('/api/layer-groups')->json('data')[0];
        $petaDasarId = $lg['id'];

        // create klasifikasi
        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial',
        ])->assertStatus(201);

        $kl = $this->getJson('/api/klasifikasi')->json('data')[0];
        $klId = $kl['id'];

        // attach data_spasial child via model to avoid file uploads
        \App\Models\DataSpasial::create([
            'klasifikasi_id' => $klId,
            'nama' => 'Danau',
            'deskripsi' => 'desc',
            'geojson_file' => '',
            'tipe_geometri' => 'polygon',
        ]);

        $resp = $this->getJson('/api/gis/search?klasifikasi_id=' . $klId);
        $resp->assertStatus(200);

        $resp->assertJson(
            fn(\Illuminate\Testing\Fluent\AssertableJson $json) => $json->has('data')
                ->where('data.id', $klId)
                ->has('data.data_spasial')
                ->etc()
        );
    }

    public function test_search_by_tipe_returns_list_of_klasifikasi()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        // create layer group & two klasifikasi
        $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta Dasar', 'deskripsi' => '', 'urutan_tampil' => 1])->assertStatus(201);
        $lg = $this->getJson('/api/layer-groups')->json('data')[0];
        $petaDasarId = $lg['id'];

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Sungai',
            'deskripsi' => 'layer sungai',
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial',
        ])->assertStatus(201);

        $this->postJson('/api/klasifikasi', [
            'nama' => 'Laut',
            'deskripsi' => 'layer laut',
            'layer_group_id' => $petaDasarId,
            'tipe' => 'data_spasial',
        ])->assertStatus(201);

        $resp = $this->getJson('/api/gis/search?tipe=data_spasial');
        $resp->assertStatus(200);

        $resp->assertJson(
            fn(\Illuminate\Testing\Fluent\AssertableJson $json) => $json->has('data')->etc()
        );

        $data = $resp->json('data');
        $this->assertTrue(count($data) >= 2, 'Expected at least two klasifikasi of type data_spasial');
    }
}
