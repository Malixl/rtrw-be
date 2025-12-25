<?php

namespace Tests\Feature;

use App\Models\DataSpasial;
use App\Models\Klasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataSpasialTest extends TestCase
{
    use RefreshDatabase;

    protected function createPrerequisites(): Klasifikasi
    {
        // Use DB inserts to ensure ids are created (models have non-standard incrementing flags)
        $periodeId = \Illuminate\Support\Facades\DB::table('periode')->insertGetId([
            'tahun_mulai' => 2020,
            'tahun_akhir' => 2025,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rtrwId = \Illuminate\Support\Facades\DB::table('rtrw')->insertGetId([
            'nama' => 'Test RTRW',
            'deskripsi' => 'desc',
            'periode_id' => $periodeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $klasifikasiId = \Illuminate\Support\Facades\DB::table('klasifikasi')->insertGetId([
            'rtrw_id' => $rtrwId,
            'nama' => 'Klas A',
            'deskripsi' => 'desc',
            'tipe' => 'pkkprl',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Klasifikasi::find($klasifikasiId);
    }

    protected function actingAdmin(): User
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_create_data_spasial_success(): void
    {
        Storage::fake('public');
        $this->actingAdmin();

        $klasifikasi = $this->createPrerequisites();
        $this->assertNotNull($klasifikasi->id, 'Klasifikasi id should not be null');

        $geojson = UploadedFile::fake()->create('feature.geojson', 10, 'application/geo+json');
        $icon = UploadedFile::fake()->image('icon.webp');

        $response = $this->post('/api/data_spasial', [
            'nama' => 'Spatial 1',
            'deskripsi' => 'desc',
            'klasifikasi_id' => $klasifikasi->id,
            'tipe_geometri' => 'polygon',
            'geojson_file' => $geojson,
            'icon_titik' => $icon,
        ]);

        if ($response->status() !== 201) {
            $this->fail('Create response failed: ' . $response->getContent());
        }

        $this->assertDatabaseHas('data_spasial', ['nama' => 'Spatial 1']);

        $record = DataSpasial::first();
        $this->assertNotEmpty($record->geojson_file);
        $this->assertTrue(Storage::disk('public')->exists($record->geojson_file));
        $this->assertTrue(Storage::disk('public')->exists($record->icon_titik));
    }

    public function test_show_geojson_returns_file(): void
    {
        Storage::fake('public');
        $this->actingAdmin();

        $klasifikasi = $this->createPrerequisites();

        // create via service through HTTP
        $geojson = UploadedFile::fake()->create('file.geojson', 10, 'application/geo+json');
        $icon = UploadedFile::fake()->image('icon.webp');

        $create = $this->post('/api/data_spasial', [
            'nama' => 'Spatial 2',
            'deskripsi' => 'desc',
            'klasifikasi_id' => $klasifikasi->id,
            'tipe_geometri' => 'polyline',
            'geojson_file' => $geojson,
            'icon_titik' => $icon,
        ]);

        if ($create->status() !== 201) {
            $this->fail('Create response failed: ' . $create->getContent());
        }

        $record = DataSpasial::first();

        $resp = $this->get('/api/data_spasial/' . $record->id . '/geojson');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'application/geo+json');
    }

    public function test_update_replaces_files(): void
    {
        Storage::fake('public');
        $this->actingAdmin();

        $klasifikasi = $this->createPrerequisites();

        $geojson1 = UploadedFile::fake()->create('file1.geojson', 10, 'application/geo+json');
        $icon1 = UploadedFile::fake()->image('icon1.webp');

        $this->post('/api/data_spasial', [
            'nama' => 'Spatial 3',
            'deskripsi' => 'desc',
            'klasifikasi_id' => $klasifikasi->id,
            'tipe_geometri' => 'point',
            'geojson_file' => $geojson1,
            'icon_titik' => $icon1,
        ])->assertStatus(201);

        $record = DataSpasial::first();
        $oldGeo = $record->geojson_file;
        $oldIcon = $record->icon_titik;

        $geojson2 = UploadedFile::fake()->create('file2.geojson', 10, 'application/geo+json');
        $icon2 = UploadedFile::fake()->image('icon2.webp');

        $this->post('/api/data_spasial/' . $record->id, [
            'nama' => 'Spatial 3 updated',
            'klasifikasi_id' => $klasifikasi->id,
            'tipe_geometri' => 'point',
            'geojson_file' => $geojson2,
            'icon_titik' => $icon2,
        ])->assertStatus(200);

        $updated = DataSpasial::find($record->id);

        $this->assertNotEquals($oldGeo, $updated->geojson_file);
        $this->assertNotEquals($oldIcon, $updated->icon_titik);

        $this->assertFalse(Storage::disk('public')->exists($oldGeo));
        $this->assertFalse(Storage::disk('public')->exists($oldIcon));
        $this->assertTrue(Storage::disk('public')->exists($updated->geojson_file));
        $this->assertTrue(Storage::disk('public')->exists($updated->icon_titik));
    }

    public function test_multi_delete_removes_records(): void
    {
        $this->actingAdmin();

        $klasifikasi = $this->createPrerequisites();

        DataSpasial::create([
            'klasifikasi_id' => $klasifikasi->id,
            'nama' => 'A',
            'deskripsi' => 'd',
            'geojson_file' => 'a.geojson',
            'tipe_geometri' => 'point',
            'icon_titik' => null,
        ]);

        DataSpasial::create([
            'klasifikasi_id' => $klasifikasi->id,
            'nama' => 'B',
            'deskripsi' => 'd',
            'geojson_file' => 'b.geojson',
            'tipe_geometri' => 'point',
            'icon_titik' => null,
        ]);

        $ids = DataSpasial::pluck('id')->toArray();

        $this->postJson('/api/data_spasial/batch', ['ids' => $ids])->assertStatus(200);

        $this->assertDatabaseMissing('data_spasial', ['nama' => 'A']);
        $this->assertDatabaseMissing('data_spasial', ['nama' => 'B']);
    }
}
