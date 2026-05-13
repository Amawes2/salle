<?php

use App\Http\Controllers\GymRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inscription-salle', [GymRegistrationController::class, 'create'])
    ->name('gyms.register');

Route::post('/inscription-salle', [GymRegistrationController::class, 'store'])
    ->name('gyms.register.store');
