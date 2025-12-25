<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LayerGroupAdditionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_validation_fails_when_nama_missing()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'sanctum');

        $payload = [
            // 'nama_layer_group' => 'No Name', // intentionally missing
            'deskripsi' => 'desc',
            'urutan_tampil' => 1,
        ];

        $resp = $this->postJson('/api/layer-groups', $payload);

        // FormRequest validation returns 422 (Unprocessable Entity)
        $resp->assertStatus(422);
        $this->assertArrayHasKey('errors', $resp->json());
    }

    public function test_opd_cannot_create_layer_group()
    {
        // create opd role and user
        Role::create(['name' => 'opd']);
        $opd = User::factory()->create();
        $opd->assignRole('opd');

        $this->actingAs($opd, 'sanctum');

        $payload = ['nama_layer_group' => 'Should Not', 'deskripsi' => '', 'urutan_tampil' => 5];
        $resp = $this->postJson('/api/layer-groups', $payload);
        $resp->assertStatus(403);
    }

    public function test_guest_cannot_create_layer_group()
    {
        $payload = ['nama_layer_group' => 'Guest', 'deskripsi' => '', 'urutan_tampil' => 5];
        $resp = $this->postJson('/api/layer-groups', $payload);
        $resp->assertStatus(401);
    }

    public function test_invalid_layer_group_id_on_klasifikasi_returns_validation_error()
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'sanctum');

        // need an RTRW and Periode
        $periodeId = \Illuminate\Support\Facades\DB::table('periode')->insertGetId([
            'tahun_mulai' => 2020,
            'tahun_akhir' => 2025,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rtrwId = \Illuminate\Support\Facades\DB::table('rtrw')->insertGetId([
            'nama' => 'RTRW Test',
            'deskripsi' => 'desc',
            'periode_id' => $periodeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'nama' => 'Test K',
            'deskripsi' => 'desc',
            'rtrw_id' => $rtrwId,
            'layer_group_id' => 999999, // invalid
            'tipe' => 'data_spasial'
        ];

        $resp = $this->postJson('/api/klasifikasi', $payload);

        // FormRequest validation returns 422
        $resp->assertStatus(422);
        $this->assertArrayHasKey('errors', $resp->json());
        $this->assertArrayHasKey('layer_group_id', $resp->json('errors'));
    }
}
