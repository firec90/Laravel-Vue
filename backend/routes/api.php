<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    Route::get('/products', [ProductController::class, 'index']);      // GET semua produk
    Route::post('/products', [ProductController::class, 'store']);     // Tambah produk
    Route::put('/products/{kode_barang}', [ProductController::class, 'update']);  // Update
    Route::delete('/products/{kode_barang}', [ProductController::class, 'destroy']); // Hapus
});
