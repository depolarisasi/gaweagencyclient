<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\CheckoutController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Domain API routes
Route::prefix('domain')->group(function () {
    Route::post('/check-availability', [DomainController::class, 'checkAvailability']);
    Route::post('/suggestions', [DomainController::class, 'getSuggestions']);
    Route::get('/supported-tlds', [DomainController::class, 'getSupportedTlds']);
    Route::post('/price', [DomainController::class, 'getPrice']);
});

// Payment API routes
Route::prefix('payment')->group(function () {
    Route::get('/status/{reference}', [CheckoutController::class, 'checkTripayStatus']);
});