<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{store_id}', [StoreController::class, 'show']);
    Route::get('/stores/{store_id}/services', [ServiceController::class, 'index']);
    
    // Slot Listing
    Route::get('/stores/{store_id}/slots', [SlotController::class, 'index']);
    
    // Slot Locking with Rate Limiting 
    // Now moved to the auth:sanctum group below

    // Booking Creation and Cancellation 
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/slots/{slot}/lock', [SlotController::class, 'lock'])->middleware('throttle:5,1');
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking_id}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking_id}/cancel', [BookingController::class, 'cancel']);
    });

    // Admin Features 
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        Route::post('/stores', [StoreController::class, 'store']);
        Route::put('/stores/{store_id}', [StoreController::class, 'update']);
        Route::patch('/stores/{store_id}/status', [StoreController::class, 'updateStatus']);
        Route::post('/stores/{store_id}/services', [ServiceController::class, 'store']);
    });
});
