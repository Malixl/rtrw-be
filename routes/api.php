<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BatasAdministrasi\BatasAdministrasiController;
use App\Http\Controllers\Berita\BeritaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\IndikasiProgram\IndikasiProgramController;
use App\Http\Controllers\KetentuanKhusus\KetentuanKhususController;
use App\Http\Controllers\Klasifikasi\klasifikasiController;
use App\Http\Controllers\Periode\PeriodeController;
use App\Http\Controllers\Pkkprl\PkkprlController;
use App\Http\Controllers\Polaruang\PolaruangController;
use App\Http\Controllers\Rtrw\RtrwController;
use App\Http\Controllers\StrukturRuang\StrukturRuangController;
use Illuminate\Support\Facades\Route;

$registerPublicRoutes = function (?string $nameSuffix = null) {
    Route::prefix('auth')->controller(AuthController::class)->group(function () use ($nameSuffix) {
        $routeName = $nameSuffix ? 'login.'.$nameSuffix : 'login';
        Route::post('login', 'login')->name($routeName);
    });

    Route::get('/berita', [BeritaController::class, 'landing']);
    Route::get('/berita/{slug}', [BeritaController::class, 'detail']);

    Route::get('/batas_administrasi', [BatasAdministrasiController::class, 'index']);
    Route::get('/batas_administrasi/{id}/geojson', [BatasAdministrasiController::class, 'showGeoJson']);

    Route::get('/rtrw', [RtrwController::class, 'index']);
    Route::get('/rtrw/{id}', [RtrwController::class, 'show']);
    Route::get('/rtrw/{id}/klasifikasi', [RtrwController::class, 'klasifikasiByRTRW']);

    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::get('/periode/{id}', [PeriodeController::class, 'show']);

    Route::get('/klasifikasi', [klasifikasiController::class, 'index']);
    Route::get('/klasifikasi/{id}', [klasifikasiController::class, 'show']);

    Route::get('/polaruang', [PolaruangController::class, 'index']);
    Route::get('/polaruang/{id}', [PolaruangController::class, 'show']);
    Route::get('/polaruang/{id}/geojson', [PolaruangController::class, 'showGeoJson']);

    Route::get('/struktur_ruang', [StrukturRuangController::class, 'index']);
    Route::get('/struktur_ruang/{id}', [StrukturRuangController::class, 'show']);
    Route::get('/struktur_ruang/{id}/geojson', [StrukturRuangController::class, 'showGeoJson']);

    Route::get('/ketentuan_khusus', [KetentuanKhususController::class, 'index']);
    Route::get('/ketentuan_khusus/{id}', [KetentuanKhususController::class, 'show']);
    Route::get('/ketentuan_khusus/{id}/geojson', [KetentuanKhususController::class, 'showGeoJson']);

    Route::get('/pkkprl', [PkkprlController::class, 'index']);
    Route::get('/pkkprl/{id}', [PkkprlController::class, 'show']);
    Route::get('/pkkprl/{id}/geojson', [PkkprlController::class, 'showGeoJson']);

    Route::get('/indikasi_program', [IndikasiProgramController::class, 'index']);
    Route::get('/indikasi_program/{id}', [IndikasiProgramController::class, 'show']);
};

$registerAuthenticatedRoutes = function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get('me', 'getUser');
        Route::post('logout', 'logout');
    });

    Route::get('/summary', [DashboardController::class, 'index']);
    Route::get('/berita-admin', [BeritaController::class, 'index']);
    Route::get('/berita-admin/{id}', [BeritaController::class, 'show']);
};

$registerAdminRoutes = function () {
    // User Management (Admin Only)
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    Route::post('/rtrw', [RtrwController::class, 'store']);
    Route::put('/rtrw/{id}', [RtrwController::class, 'update']);
    Route::delete('/rtrw/multi-delete', [RtrwController::class, 'multiDestroy']);
    Route::delete('/rtrw/{id}', [RtrwController::class, 'destroy']);

    Route::post('/periode', [PeriodeController::class, 'store']);
    Route::put('/periode/{id}', [PeriodeController::class, 'update']);
    Route::delete('/periode/multi-delete', [PeriodeController::class, 'multiDestroy']);
    Route::delete('/periode/{id}', [PeriodeController::class, 'destroy']);

    Route::post('/klasifikasi', [klasifikasiController::class, 'store']);
    Route::put('/klasifikasi/{id}', [klasifikasiController::class, 'update']);
    Route::delete('/klasifikasi/multi-delete', [klasifikasiController::class, 'multiDestroy']);
    Route::delete('/klasifikasi/{id}', [klasifikasiController::class, 'destroy']);

    Route::post('/batas_administrasi', [BatasAdministrasiController::class, 'store']);
    Route::put('/batas_administrasi/{id}', [BatasAdministrasiController::class, 'update']);
    Route::delete('/batas_administrasi/multi-delete', [BatasAdministrasiController::class, 'multiDestroy']);
    Route::delete('/batas_administrasi/{id}', [BatasAdministrasiController::class, 'destroy']);

    Route::post('/polaruang', [PolaruangController::class, 'store']);
    Route::post('/polaruang/batch', [PolaruangController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/polaruang/{id}', [PolaruangController::class, 'update']);
    Route::delete('/polaruang/multi-delete', [PolaruangController::class, 'multiDestroy']);
    Route::delete('/polaruang/{id}', [PolaruangController::class, 'destroy']);

    Route::post('/struktur_ruang', [StrukturRuangController::class, 'store']);
    Route::post('/struktur_ruang/batch', [StrukturRuangController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/struktur_ruang/{id}', [StrukturRuangController::class, 'update']);
    Route::delete('/struktur_ruang/multi-delete', [StrukturRuangController::class, 'multiDestroy']);
    Route::delete('/struktur_ruang/{id}', [StrukturRuangController::class, 'destroy']);

    Route::post('/ketentuan_khusus', [KetentuanKhususController::class, 'store']);
    Route::post('/ketentuan_khusus/batch', [KetentuanKhususController::class, 'multiDestroy']);
    Route::post('/ketentuan_khusus/{id}', [KetentuanKhususController::class, 'update']);
    Route::delete('/ketentuan_khusus/multi-delete', [KetentuanKhususController::class, 'multiDestroy']);
    Route::delete('/ketentuan_khusus/{id}', [KetentuanKhususController::class, 'destroy']);

    Route::post('/pkkprl', [PkkprlController::class, 'store']);
    Route::post('/pkkprl/batch', [PkkprlController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/pkkprl/{id}', [PkkprlController::class, 'update']);
    Route::delete('/pkkprl/multi-delete', [PkkprlController::class, 'multiDestroy']);
    Route::delete('/pkkprl/{id}', [PkkprlController::class, 'destroy']);

    Route::post('/indikasi_program', [IndikasiProgramController::class, 'store']);
    Route::post('/indikasi_program/batch', [IndikasiProgramController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/indikasi_program/{id}', [IndikasiProgramController::class, 'update']);
    Route::delete('/indikasi_program/multi-delete', [IndikasiProgramController::class, 'multiDestroy']);
    Route::delete('/indikasi_program/{id}', [IndikasiProgramController::class, 'destroy']);

    Route::post('/berita', [BeritaController::class, 'store']);
    Route::post('/berita/{id}', [BeritaController::class, 'update']);
    Route::delete('/berita/multi-delete', [BeritaController::class, 'multiDestroy']);
    Route::delete('/berita/{id}', [BeritaController::class, 'destroy']);
};

$registerPublicRoutes();

Route::middleware(['auth:sanctum'])->group($registerAuthenticatedRoutes);

Route::middleware(['auth:sanctum', 'role:admin'])->group($registerAdminRoutes);

Route::prefix('v1')->group(function () use ($registerPublicRoutes, $registerAuthenticatedRoutes, $registerAdminRoutes) {
    $registerPublicRoutes('v1');

    Route::middleware(['auth:sanctum'])->group($registerAuthenticatedRoutes);

    Route::middleware(['auth:sanctum', 'role:admin'])->group($registerAdminRoutes);
});
