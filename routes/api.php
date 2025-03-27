<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\PengirimanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
            ->except(['index', 'store']);
        });
    });
    Route::middleware('can:kurir')->group(function () {
        Route::get('kurir', [KurirController::class, 'get'])->withoutMiddleware('can:kurir');
        Route::post('kurir/store', [KurirController::class, 'store']);
        Route::post('kurir', [KurirController::class, 'index']);
        // Route::get('/kurir/get', [KurirController::class, 'get']);
        Route::apiResource('kurir', KurirController::class)
        ->except(['index', 'store']);
    });
    Route::prefix('kurir')->group(function () {
        Route::get('/', [KurirController::class, 'index']); // List kurir dengan pagination
        Route::post('/store', [KurirController::class, 'store']); // Tambah kurir
        // Route::get('/{kurir}', [KurirController::class, 'show']); // Lihat detail kurir
        // Route::put('/{kurir}', [KurirController::class, 'update']); // Update kurir
        // Route::delete('/{kurir}', [KurirController::class, 'destroy']); // Hapus kurir
    });
    Route::middleware('can:pengiriman')->group(function () {
        Route::get('pengiriman', [PengirimanController::class, 'get'])->withoutMiddleware('can:pengiriman');
        // Route::get('/pengiriman', [PengirimanController::class, 'get'])->withoutMiddleware(['can:pengiriman']);
        Route::post('pengiriman/store', [PengirimanController::class, 'store']);
        Route::post('pengiriman', [PengirimanController::class, 'index']);
        Route::apiResource('pengiriman', PengirimanController::class)
        ->except(['index', 'store']);
    });

});
