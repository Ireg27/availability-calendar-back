<?php

use App\Http\Controllers\Api\UnavailabilityController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

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

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users/{user}/unavailabilities', [UnavailabilityController::class, 'index']);
    Route::put('users/{user}/unavailability', [UnavailabilityController::class, 'toggle']);
    Route::post('users/{user}/unavailabilities', [UnavailabilityController::class, 'setUnavailabilityForAll']);
    Route::post('/users/{user}/unavailabilities/check-availability', [UnavailabilityController::class, 'checkAvailability']);

});
