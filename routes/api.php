<?php

use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WeatherController;
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

Route::get('weather', [WeatherController::class, 'index'])->name('weather');
Route::post('subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
Route::get('confirm/{token}', [SubscriptionController::class, 'confirm'])->name('confirm');
Route::get('unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])->name('unsubscribe');
