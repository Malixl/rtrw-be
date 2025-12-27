<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayerGroupStoreReturnsResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_layer_group_returns_full_resource()
    {
        $this->artisan('migrate:fresh --seed');

        if (! \Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        }

        $user = \App\Models\User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        $resp = $this->postJson('/api/layer-groups', ['layer_group_name' => 'Peta X', 'deskripsi' => 'desc', 'urutan_tampil' => 5]);
        $resp->assertStatus(201);

        $json = $resp->json('data');

        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('layer_group_name', $json);
        $this->assertEquals('Peta X', $json['layer_group_name']);

        $this->assertArrayHasKey('klasifikasis', $json);
        $this->assertArrayHasKey('klasifikasi_pola_ruang', $json['klasifikasis']);
        $this->assertIsArray($json['klasifikasis']['klasifikasi_pola_ruang']);
    }
}
