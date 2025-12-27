<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\BatasAdministrasi\BatasAdministrasiController;
use App\Http\Controllers\Berita\BeritaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DataSpasial\DataSpasialController;
use App\Http\Controllers\IndikasiProgram\IndikasiProgramController;
use App\Http\Controllers\KetentuanKhusus\KetentuanKhususController;
use App\Http\Controllers\Klasifikasi\KlasifikasiController;
use App\Http\Controllers\LayerGroup\LayerGroupController;
use App\Http\Controllers\Pkkprl\PkkprlController;
use App\Http\Controllers\Polaruang\PolaruangController;
use App\Http\Controllers\StrukturRuang\StrukturRuangController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - RBAC Structure
|--------------------------------------------------------------------------
| A. PUBLIC ROUTES (Guest - No Token Required)
|    - Login, Berita Landing, Guest Capabilities
| B. AUTHENTICATED ROUTES (Admin + OPD - Token Required)
|    - Get User, Logout, View Map Data, Dashboard (Admin only)
| C. ADMIN ONLY ROUTES (Admin Only - Token + Role Check)
|    - CRUD Operations, User Management
|--------------------------------------------------------------------------
*/

$registerPublicRoutes = function (?string $nameSuffix = null) {
    // Auth Routes
    Route::prefix('auth')->controller(AuthController::class)->group(function () use ($nameSuffix) {
        $routeName = $nameSuffix ? 'login.'.$nameSuffix : 'login';
        Route::post('login', 'login')->name($routeName);
    });

    // Role Check (untuk guest capabilities)
    Route::get('/role/guest', [RoleController::class, 'guestCapabilities']);

    // Public Berita
    Route::get('/berita', [BeritaController::class, 'landing']);
    Route::get('/berita/{slug}', [BeritaController::class, 'detail']);

    // Alias untuk landing berita (frontend compatibility)
    Route::get('/landing/berita', [BeritaController::class, 'landing']);
    Route::get('/landing/berita/{slug}', [BeritaController::class, 'detail']);

    // Public Map Data (Read Only - untuk OPD dan preview)
    Route::get('/batas_administrasi', [BatasAdministrasiController::class, 'index']);
    Route::get('/batas_administrasi/{id}/geojson', [BatasAdministrasiController::class, 'showGeoJson']);

    // /rtrw and /periode endpoints removed — use search and klasifikasi-based loading instead

    Route::get('/layer-groups', [LayerGroupController::class, 'index']);
    Route::get('/layer-groups/with-klasifikasi', [LayerGroupController::class, 'withKlasifikasi']);
    Route::get('/layer-groups/{id}', [LayerGroupController::class, 'show']);

    // GIS search endpoint - replaces RTRW-driven search
    Route::get('/gis/search', [LayerGroupController::class, 'search']);

    Route::get('/klasifikasi', [KlasifikasiController::class, 'index']);
    Route::get('/klasifikasi/{id}', [KlasifikasiController::class, 'show']);

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

    // Data Spasial (mirip PKKPRL - public read)
    Route::get('/data_spasial', [DataSpasialController::class, 'index']);
    Route::get('/data_spasial/{id}', [DataSpasialController::class, 'show']);
    Route::get('/data_spasial/{id}/geojson', [DataSpasialController::class, 'showGeoJson']);

    Route::get('/indikasi_program', [IndikasiProgramController::class, 'index']);
    Route::get('/indikasi_program/{id}', [IndikasiProgramController::class, 'show']);
};

$registerAuthenticatedRoutes = function () {
    // Auth Routes (Authenticated)
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get('me', 'getUser');
        Route::post('logout', 'logout');
    });

    // Role Check (Authenticated)
    Route::get('/role/check', [RoleController::class, 'checkRole']);

    // Dashboard (Admin + OPD dapat akses summary, tapi OPD read-only)
    Route::get('/summary', [DashboardController::class, 'index']);

    // Berita Admin View (Admin + OPD)
    Route::get('/berita-admin', [BeritaController::class, 'index']);
    Route::get('/berita-admin/{id}', [BeritaController::class, 'show']);
};

$registerAdminRoutes = function () {
    // Role Capabilities (Admin Only)
    Route::get('/role/all-capabilities', [RoleController::class, 'allCapabilities']);

    // RTRW and Periode CRUD removed (modules deprecated)

    // Klasifikasi CRUD
    Route::post('/klasifikasi', [KlasifikasiController::class, 'store']);
    Route::put('/klasifikasi/{id}', [KlasifikasiController::class, 'update']);
    Route::delete('/klasifikasi/multi-delete', [KlasifikasiController::class, 'multiDestroy']);
    Route::delete('/klasifikasi/{id}', [KlasifikasiController::class, 'destroy']);

    // LayerGroup CRUD
    Route::post('/layer-groups', [LayerGroupController::class, 'store']);
    Route::put('/layer-groups/{id}', [LayerGroupController::class, 'update']);
    Route::delete('/layer-groups/multi-delete', [LayerGroupController::class, 'multiDestroy']);
    Route::delete('/layer-groups/{id}', [LayerGroupController::class, 'destroy']);

    // Batas Administrasi CRUD
    Route::post('/batas_administrasi', [BatasAdministrasiController::class, 'store']);
    Route::put('/batas_administrasi/{id}', [BatasAdministrasiController::class, 'update']);
    Route::delete('/batas_administrasi/multi-delete', [BatasAdministrasiController::class, 'multiDestroy']);
    Route::delete('/batas_administrasi/{id}', [BatasAdministrasiController::class, 'destroy']);

    // Polaruang CRUD
    Route::post('/polaruang', [PolaruangController::class, 'store']);
    Route::post('/polaruang/batch', [PolaruangController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/polaruang/{id}', [PolaruangController::class, 'update']);
    Route::delete('/polaruang/multi-delete', [PolaruangController::class, 'multiDestroy']);
    Route::delete('/polaruang/{id}', [PolaruangController::class, 'destroy']);

    // Struktur Ruang CRUD
    Route::post('/struktur_ruang', [StrukturRuangController::class, 'store']);
    Route::post('/struktur_ruang/batch', [StrukturRuangController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/struktur_ruang/{id}', [StrukturRuangController::class, 'update']);
    Route::delete('/struktur_ruang/multi-delete', [StrukturRuangController::class, 'multiDestroy']);
    Route::delete('/struktur_ruang/{id}', [StrukturRuangController::class, 'destroy']);

    // Ketentuan Khusus CRUD
    Route::post('/ketentuan_khusus', [KetentuanKhususController::class, 'store']);
    Route::post('/ketentuan_khusus/batch', [KetentuanKhususController::class, 'multiDestroy']);
    Route::post('/ketentuan_khusus/{id}', [KetentuanKhususController::class, 'update']);
    Route::delete('/ketentuan_khusus/multi-delete', [KetentuanKhususController::class, 'multiDestroy']);
    Route::delete('/ketentuan_khusus/{id}', [KetentuanKhususController::class, 'destroy']);

    // PKKPRL CRUD
    Route::post('/pkkprl', [PkkprlController::class, 'store']);
    Route::post('/pkkprl/batch', [PkkprlController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/pkkprl/{id}', [PkkprlController::class, 'update']);
    Route::delete('/pkkprl/multi-delete', [PkkprlController::class, 'multiDestroy']);
    Route::delete('/pkkprl/{id}', [PkkprlController::class, 'destroy']);

    // Data Spasial CRUD (mirip PKKPRL)
    Route::post('/data_spasial', [DataSpasialController::class, 'store']);
    Route::post('/data_spasial/batch', [DataSpasialController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/data_spasial/{id}', [DataSpasialController::class, 'update']);
    Route::delete('/data_spasial/multi-delete', [DataSpasialController::class, 'multiDestroy']);
    Route::delete('/data_spasial/{id}', [DataSpasialController::class, 'destroy']);

    // Indikasi Program CRUD
    Route::post('/indikasi_program', [IndikasiProgramController::class, 'store']);
    Route::post('/indikasi_program/batch', [IndikasiProgramController::class, 'multiDestroy']);
    Route::match(['post', 'put'], '/indikasi_program/{id}', [IndikasiProgramController::class, 'update']);
    Route::delete('/indikasi_program/multi-delete', [IndikasiProgramController::class, 'multiDestroy']);
    Route::delete('/indikasi_program/{id}', [IndikasiProgramController::class, 'destroy']);

    // Berita CRUD
    Route::post('/berita', [BeritaController::class, 'store']);
    Route::post('/berita/{id}', [BeritaController::class, 'update']);
    Route::delete('/berita/multi-delete', [BeritaController::class, 'multiDestroy']);
    Route::delete('/berita/{id}', [BeritaController::class, 'destroy']);

    // User Management (Admin Only)
    Route::delete('/admin/users/multi-delete', [UserController::class, 'multiDestroy']);
    Route::apiResource('admin/users', UserController::class);
};

// ============================================
// REGISTER ROUTES
// ============================================

// Public Routes (Guest Access)
$registerPublicRoutes();

// Authenticated Routes (Admin + OPD)
Route::middleware(['auth:sanctum'])->group($registerAuthenticatedRoutes);

// Admin Only Routes
Route::middleware(['auth:sanctum', 'role:admin'])->group($registerAdminRoutes);

// Versioned API (v1)
Route::prefix('v1')->group(function () use ($registerPublicRoutes, $registerAuthenticatedRoutes, $registerAdminRoutes) {
    $registerPublicRoutes('v1');

    Route::middleware(['auth:sanctum'])->group($registerAuthenticatedRoutes);

    Route::middleware(['auth:sanctum', 'role:admin'])->group($registerAdminRoutes);
});
