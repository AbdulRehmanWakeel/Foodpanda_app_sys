<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RiderController;

 
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (JWT required)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
    });
});

// Admin-only routes
Route::prefix('admin')->middleware(['auth:api'])->group(function () {
    Route::get('/users', [AdminController::class, 'users']); 
    Route::get('/restaurants', [AdminController::class, 'restaurants']); 
    Route::get('/riders', [AdminController::class, 'riders']); 
    Route::get('/orders', [AdminController::class, 'orders']); 
    Route::get('/analytics', [AdminController::class, 'analytics']); 
});

// Restaurant routes
Route::prefix('restaurant')->middleware(['auth:api'])->group(function () {

    Route::get('restaurants', [RestaurantController::class, 'index']);       
    Route::get('restaurants/{id}', [RestaurantController::class, 'show']);  
    Route::post('restaurants', [RestaurantController::class, 'store']); 
    Route::get('/orders', [RestaurantController::class, 'orders']);
    Route::patch('/orders/{id}/status', [RestaurantController::class, 'updateOrderStatus']); 

    Route::post('/menu', [MenuController::class, 'store']);
    Route::patch('/menu/{id}', [MenuController::class, 'update']);
    Route::delete('/menu/{id}', [MenuController::class, 'destroy']); 

});
// Rider routes

Route::prefix('riders')->group(function () {
    Route::post('/register', [RiderController::class, 'register']);
    Route::post('/login', [RiderController::class, 'login']);
});

Route::prefix('riders')->middleware(['auth:api'])->group(function () {
    Route::post('/logout', [RiderController::class, 'logout']);
    Route::post('/status', [RiderController::class, 'updateStatus']);
    Route::get('/orders', [RiderController::class, 'assignedOrders']);
    Route::patch('/orders/{id}', [RiderController::class, 'updateOrderStatus']);
    Route::get('/earnings', [RiderController::class, 'earnings']);
});



