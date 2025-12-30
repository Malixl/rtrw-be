<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Klasifikasi;
use App\Models\BatasAdministrasi;
use Illuminate\Http\Request;

class KlasifikasiBatasAdministrasiTest extends TestCase
{
    public function test_klasifikasi_map_resources_includes_batas_administrasi()
    {
        $kl = Klasifikasi::make([
            'id' => 99999,
            'nama' => 'Batas Administrasi',
            'deskripsi' => 'Test klasifikasi batas',
            'tipe' => 'batas_administrasi',
        ]);

        $ba = BatasAdministrasi::make([
            'id' => 88888,
            'nama' => 'Provinsi',
            'deskripsi' => 'Contoh',
            'geojson_file' => 'batas/prov.geojson',
            'tipe_geometri' => 'polyline',
            'tipe_garis' => 'solid',
            'warna' => '#000000',
        ]);

        $kl->setRelation('batasAdministrasi', collect([$ba]));

        $request = Request::create('/', 'GET', ['raw_dates' => 'true']);

        $arr = (new \App\Http\Resources\KlasifikasiMapResources($kl))->toArray($request);

        $this->assertArrayHasKey('batas_administrasi', $arr);
        $this->assertIsArray($arr['batas_administrasi']);
        $this->assertCount(1, $arr['batas_administrasi']);
    }
}
