<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Polaruang;
use App\Models\Klasifikasi;
use App\Http\Resources\PolaruangResources;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResourceSerializationTest extends TestCase
{
    public function test_polaruang_resource_outputs_klasifikasi_id_and_iso_dates()
    {
        $kl = Klasifikasi::make([
            'id' => 1,
            'nama' => 'Pola ruang 1',
            'deskripsi' => 'Contoh klasifikasi',
            'tipe' => 'pola_ruang',
        ]);

        $pr = Polaruang::make([
            'id' => 1,
            'nama' => 'Polaruang 1',
            'warna' => '#FF000',
            'deskripsi' => 'Contoh polaruang',
            'geojson_file' => 'polaruang/sample.geojson',
            'klasifikasi_id' => 1,
            'created_at' => Carbon::parse('2025-12-29T13:01:17.000000Z'),
            'updated_at' => Carbon::parse('2025-12-29T13:01:17.000000Z'),
        ]);

        // attach relation
        $pr->setRelation('klasifikasi', $kl);
        // ensure timestamps accessible
        $pr->created_at = Carbon::parse('2025-12-29T13:01:17.000000Z');
        $pr->updated_at = Carbon::parse('2025-12-29T13:01:17.000000Z');

        $request = Request::create('/', 'GET', ['raw_dates' => 'true']);

        $arr = (new PolaruangResources($pr))->toArray($request);

        $this->assertArrayHasKey('klasifikasi_id', $arr);
        $this->assertEquals(1, $arr['klasifikasi_id']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertNotNull($arr['created_at']);
        $this->assertIsString($arr['created_at']);
    }
}
