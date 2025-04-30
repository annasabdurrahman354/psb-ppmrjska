<?php

use App\Http\Controllers\Inertia\PendaftaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pendaftaran');
});

Route::get('/pendaftaran/create', \App\Livewire\Pendaftaran\PendaftaranCreate::class)->name('pendaftaran.create');

Route::get('/pendaftaran/', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
Route::get('/pendaftaran/finish/{id}', [PendaftaranController::class, 'finish'])->name('pendaftaran.finish');


