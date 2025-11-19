<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyCelebrationController;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['role:verifier'])
    ->prefix('admin/celebrations')
    ->group(function () {
        Route::get('{celebration}/verify', [VerifyCelebrationController::class, 'show'])
           ->name('celebrations.verify.show');
        Route::post('{celebration}/verify', [VerifyCelebrationController::class, 'verify'])
            ->name('celebrations.verify.submit');
    });

