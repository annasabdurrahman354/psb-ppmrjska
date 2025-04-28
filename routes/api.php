<?php

use App\Http\Controllers\Api\OptionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('options')->group(function () {
    Route::get('/kota', [OptionsController::class, 'getKota'])->name('kota');
    Route::get('/kecamatan', [OptionsController::class, 'getKecamatan'])->name('kecamatan');
    Route::get('/kelurahan', [OptionsController::class, 'getKelurahan'])->name('kelurahan');
});

