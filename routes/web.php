<?php

use App\Http\Controllers\Inertia\PendaftaranController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pendaftaran');
});

Route::get('/pendaftaran/create', \App\Livewire\Pendaftaran\PendaftaranCreate::class)->name('pendaftaran.create');

Route::get('/pendaftaran/', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
Route::get('/pendaftaran/finish/{id}', [PendaftaranController::class, 'finish'])->name('pendaftaran.finish');

Route::get('/command/storage/link', function () {
    Artisan::call('storage:link');
    dd("Storage is linked");
});

Route::get('/command/filament/optimize', function () {
    Artisan::call('filament:optimize');
    dd("Filament is optimized");
});

Route::get('/command/filament/optimize-clear', function () {
    Artisan::call('filament:optimize-clear');
    dd("Filament cache is cleared");
});

