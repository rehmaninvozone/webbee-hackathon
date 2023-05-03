<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(\App\Http\Controllers\Api\V1\AppointmentController::class)->group(function () {
    Route::post('/get-available-slots', 'getAvailableSlots');
    Route::post('/book-appointment', 'bookAppointment')->name('bookAppointment');

});
Route::apiResource('schedulings', \App\Http\Controllers\Api\V1\SchedulingController::class);
Route::apiResource('services', \App\Http\Controllers\Api\V1\ServiceController::class);
