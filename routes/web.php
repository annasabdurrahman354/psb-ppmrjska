<?php

use App\Http\Controllers\Inertia\PendaftaranCalonSantriController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Example for displaying the form (HTTP GET request)
Route::get('/pendaftaran/santri', [PendaftaranCalonSantriController::class, 'create']);
// Example for handling the form submission (HTTP POST request)
Route::post('/pendaftaran/santri', [PendaftaranCalonSantriController::class, 'store']);
// Example for the success page (HTTP GET request)
Route::get('/pendaftaran/sukses', [PendaftaranCalonSantriController::class, 'sukses']);
