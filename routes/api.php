<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------
| API Routes
|----------------------------------------------------------------------
| API routes for your application.
*/

// Authentication Routes
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// Settings Routes
Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    // Settings Update Route
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        // User Routes
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        // Role Routes
        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get']);
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

    });
});

Route::prefix('tambah')->group(function () {
    // Barang Kategori
    Route::middleware('can:tambah-kategori')->group(function () {
        Route::get('kategori', [KategoriController::class, 'get']);
        Route::post('kategori', [KategoriController::class, 'index']);
        // Route::put('/kategori/{kategori}', [KategoriController::class, 'update']);
        Route::post('kategori/store', [KategoriController::class, 'store']);
        // Route::apiResource('tambah/kategori', KategoriController::class);
        Route::apiResource('kategori', KategoriController::class)
        ->except(['index', 'store']);
    });  
    Route::middleware('can:tambah-barang')->group(function () {
        Route::get('barang', [BarangController::class, 'get']);
        Route::post('barang', [BarangController::class, 'index']);
        Route::post('barang/store', [BarangController::class, 'store']);
        Route::apiResource('tambah/barang', BarangController::class);
        Route::apiResource('barang', BarangController::class)
        ->except(['index', 'store']);
    });      
});

Route::middleware('can:history')->group(function () {
    Route::get('history', [HistoryController::class, 'get']);
    Route::get('/history/detail/{id}', [HistoryController::class, 'detail']);
    // Route::get('/history/detail/{id}', [HistoryController::class, 'show']);
    Route::post('history', [HistoryController::class, 'index']);
    Route::post('history/store', [HistoryController::class, 'store']);
    Route::apiResource('barang', HistoryController::class)
    ->except(['index', 'store']);
});    

Route::middleware('can:pembayaran')->group(function () {
    // Rute ambil data transaksi (misalnya untuk list)
    Route::get('transaksi', [TransaksiController::class, 'index']);
    // Rute simpan transaksi baru
    Route::post('/transaksi/store', [TransaksiController::class, 'store']);
    // Rute ambil detail transaksi tertentu (jika diperlukan)
    Route::get('transaksi/{id}', [TransaksiController::class, 'show']);
    // Rute update transaksi
    Route::put('transaksi/{id}', [TransaksiController::class, 'update']);
    // Rute hapus transaksi
    Route::delete('transaksi/{id}', [TransaksiController::class, 'destroy']);
});

// Route::middleware(['auth:sanctum', 'verified', 'json'])
//     ->prefix('history')
//     ->middleware('can:history')
//     ->group(function () {
//         Route::get('/detail_transaksi', [HistoryController::class, 'detailTransaksi']); // <- Pindahkan ke atas
//         Route::get('/', [HistoryController::class, 'index']);           // Ambil semua data history
//         Route::post('/', [HistoryController::class, 'store']);          // Simpan data history baru
//         Route::get('/{id}', [HistoryController::class, 'show']);        // Lihat detail history
//         Route::put('/{id}', [HistoryController::class, 'update']);      // Update data history
//         Route::delete('/{id}', [HistoryController::class, 'destroy']);  // Hapus history
//     });


