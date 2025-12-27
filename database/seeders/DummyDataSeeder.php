<?php

namespace Database\Seeders;

use App\Models\BatasAdministrasi;
use App\Models\DataSpasial;
use App\Models\IndikasiProgram;
use App\Models\Klasifikasi;
use App\Models\LayerGroup;
use App\Models\KetentuanKhusus;
use App\Models\Polaruang;
use App\Models\Pkkprl;
use App\Models\StrukturRuang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create 2 LayerGroups
        $lg1 = LayerGroup::create([
            'nama_layer_group' => 'Peta Contoh A',
            'deskripsi' => 'Layer group contoh A',
            'urutan_tampil' => 1,
        ]);

        $lg2 = LayerGroup::create([
            'nama_layer_group' => 'Peta Contoh B',
            'deskripsi' => 'Layer group contoh B',
            'urutan_tampil' => 2,
        ]);

        $types = [
            'pola_ruang',
            'struktur_ruang',
            'ketentuan_khusus',
            'indikasi_program',
            'pkkprl',
            'data_spasial',
        ];

        foreach ($types as $type) {
            // create 2 klasifikasi per tipe
            for ($i = 1; $i <= 2; $i++) {
                // Insert klasifikasi and retrieve auto-increment id (migration uses integer id)
                $klasifikasiId = \Illuminate\Support\Facades\DB::table('klasifikasi')->insertGetId([
                    'layer_group_id' => $i % 2 ? $lg1->id : $lg2->id,
                    'nama' => ucfirst(str_replace('_', ' ', $type)) . " $i",
                    'deskripsi' => "Contoh klasifikasi $type $i",
                    'tipe' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // debug output
                if (method_exists($this, 'command') && $this->command) {
                    $this->command->info('Created klasifikasi: ' . ucfirst(str_replace('_', ' ', $type)) . " $i" . ' id=' . $klasifikasiId);
                }

                // Prepare sample geojson files
                $samplePolygon = null;
                $samplePoint = null;
                $sampleDoc = null;

                if (file_exists(base_path('postman/imports/sample_polygons.geojson'))) {
                    $samplePolygon = file_get_contents(base_path('postman/imports/sample_polygons.geojson'));
                }

                if (file_exists(base_path('postman/imports/data_spasial_sample.geojson'))) {
                    $samplePoint = file_get_contents(base_path('postman/imports/data_spasial_sample.geojson'));
                }

                if (file_exists(base_path('postman/imports/sample_doc.txt'))) {
                    $sampleDoc = file_get_contents(base_path('postman/imports/sample_doc.txt'));
                }

                // Create one or two child entries depending on tipe
                switch ($type) {
                    case 'pola_ruang':
                        // save a copy to public disk and reference path
                        $filePath = '';
                        if ($samplePolygon) {
                            $filePath = 'polaruang/sample_polygon_' . $i . '.geojson';
                            \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $samplePolygon);
                        }

                        Polaruang::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "Polaruang $i",
                            'deskripsi' => 'Contoh polaruang',
                            'warna' => '#FF000',
                            'geojson_file' => $filePath,
                        ]);

                        break;
                    case 'struktur_ruang':
                        StrukturRuang::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "Struktur $i",
                            'deskripsi' => 'Contoh struktur ruang',
                            'tipe_geometri' => 'point',
                            'icon_titik' => 'default.png',
                            'tipe_garis' => null,
                            'warna' => '#00FF00',
                            'geojson_file' => '',
                        ]);

                        break;
                    case 'ketentuan_khusus':
                        KetentuanKhusus::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "Ketentuan $i",
                            'deskripsi' => 'Contoh ketentuan khusus',
                            'tipe_geometri' => 'polygon',
                            'icon_titik' => 'default.png',
                            'tipe_garis' => null,
                            'warna' => '#0000FF',
                            'geojson_file' => '',
                        ]);

                        break;
                    case 'indikasi_program':
                        // save sample doc
                        $docFile = '';
                        if ($sampleDoc) {
                            $docFile = 'indikasi_program/sample_doc_' . $i . '.txt';
                            \Illuminate\Support\Facades\Storage::disk('public')->put($docFile, $sampleDoc);
                        }

                        IndikasiProgram::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "Indikasi $i",
                            'file_dokumen' => $docFile,
                        ]);

                        break;
                    case 'pkkprl':
                        Pkkprl::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "PKKPRL $i",
                            'deskripsi' => 'Contoh PKKPRL',
                            'tipe_geometri' => 'polygon',
                            'icon_titik' => 'default.png',
                            'tipe_garis' => null,
                            'warna' => '#FFA500',
                            'geojson_file' => '',
                        ]);

                        break;
                    case 'data_spasial':
                        $dsFile = '';
                        if ($samplePoint) {
                            $dsFile = 'data_spasial/sample_point_' . $i . '.geojson';
                            \Illuminate\Support\Facades\Storage::disk('public')->put($dsFile, $samplePoint);
                        }

                        DataSpasial::create([
                            'klasifikasi_id' => $klasifikasiId,
                            'nama' => "DataSpasial $i",
                            'deskripsi' => 'Contoh data spasial',
                            'tipe_geometri' => 'point',
                            'icon_titik' => 'default.png',
                            'tipe_garis' => null,
                            'warna' => '#CCCCCC',
                            'geojson_file' => $dsFile,
                        ]);

                        break;
                }
            }
        }

        // Create two Batas Administrasi
        BatasAdministrasi::create([
            'nama' => 'Batas Administrasi 1',
            'deskripsi' => 'Contoh batas administrasi 1',
            'geojson_file' => '',
            'tipe_geometri' => 'polygon',
            'tipe_garis' => null,
            'warna' => '#123456',
        ]);

        BatasAdministrasi::create([
            'nama' => 'Batas Administrasi 2',
            'deskripsi' => 'Contoh batas administrasi 2',
            'geojson_file' => '',
            'tipe_geometri' => 'polygon',
            'tipe_garis' => null,
            'warna' => '#654321',
        ]);
    }
}
