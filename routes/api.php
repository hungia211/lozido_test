<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PriceItemController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

// Module user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Xem phòng
Route::get('/rooms/{id}', [RoomController::class, 'show'])->where('id', '[0-9]+');

Route::middleware('auth:sanctum')->group(function () {

    // Module phòng 
    Route::get('/rooms', [RoomController::class, 'showAll']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->where('id', '[0-9]+');

    // Module Dịch vụ phòng
    Route::post('/rooms/{id}/price-items', [RoomController::class, 'attachPriceItem']);
    Route::delete('/rooms/{id}/price-items', [RoomController::class, 'detachPriceItem']);

    // Module khách hàng
    Route::post('/customers', [CustomerController::class, 'store']);

    // Module dịch vụ 
    Route::get('/price-items', [PriceItemController::class, 'show']);
    Route::post('/price-items', [PriceItemController::class, 'store']);
    Route::delete('/price-items/{id}', [PriceItemController::class, 'destroy']);
});
