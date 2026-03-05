<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/mpesa/callback', [WalletController::class, 'mpesaCallback'])->name('mpesa.callback');
Route::get('/products/latest', [BarimaxAdController::class, 'getLatestProduct']);
// Route::post('/wallet/deposit', [WalletController::class, 'processDeposit']);