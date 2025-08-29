<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ErrorController;

// ================== Auth routes ==================
Route::prefix('auth')->group(function () {
    // Public routes (all roles)
    Route::post('/register', [AuthController::class, 'register']); // dynamically assign role: customer, restaurant, rider
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (JWT required)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
    });
});

Route::prefix('admin')->middleware(['auth:api','role:admin'])->group(function () {
    // lists & analytics
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/restaurants', [AdminController::class, 'restaurants']);
    Route::get('/riders', [AdminController::class, 'riders']);
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::get('/analytics', [AdminController::class, 'analytics']);

    // users CRUD
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    // restaurants CRUD + approvals
    Route::post('/restaurants', [AdminController::class, 'storeRestaurant']);
    Route::put('/restaurants/{id}', [AdminController::class, 'updateRestaurant']);
    Route::delete('/restaurants/{id}', [AdminController::class, 'deleteRestaurant']);
    Route::post('/restaurants/{id}/approve', [AdminController::class, 'approveRestaurant']);
    Route::post('/restaurants/{id}/reject', [AdminController::class, 'rejectRestaurant']);

    // riders CRUD + verify/reject
    Route::post('/riders', [AdminController::class, 'storeRider']);
    Route::put('/riders/{id}', [AdminController::class, 'updateRider']);
    Route::delete('/riders/{id}', [AdminController::class, 'deleteRider']);
    Route::post('/riders/{id}/verify', [AdminController::class, 'verifyRider']);
    Route::post('/riders/{id}/reject', [AdminController::class, 'rejectRider']);
});
// ================== Restaurant Panel ==================
Route::prefix('restaurant')->middleware(['auth:api','role:restaurant'])->group(function () {
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::get('restaurants/{id}', [RestaurantController::class, 'show']);
    Route::post('restaurants', [RestaurantController::class, 'store']);
    Route::get('restaurants/{id}/stats', [RestaurantController::class, 'stats']);

    // Orders
    Route::get('/restaurant/orders', [RestaurantController::class, 'orders']);
    Route::patch('/orders/{id}/status', [RestaurantController::class, 'updateOrderStatus']);

    // Menu CRUD
    Route::get('/menu/{restaurantId}', [MenuController::class, 'index']);
    Route::get('/menu/item/{id}', [MenuController::class, 'show']);
    Route::post('/menu', [MenuController::class, 'store']);
    Route::patch('/menu/{id}', [MenuController::class, 'update']);
    Route::delete('/menu/{id}', [MenuController::class, 'destroy']);
});


// ================== Rider Panel ==================
Route::prefix('riders')->group(function () {
    Route::post('/register', [RiderController::class, 'register']);
    Route::post('/login', [RiderController::class, 'login']);
});

Route::prefix('riders')->middleware(['auth:api','role:rider'])->group(function () {
    Route::post('/logout', [RiderController::class, 'logout']);
    Route::post('/status', [RiderController::class, 'updateStatus']);
    Route::get('/orders', [RiderController::class, 'assignedOrders']);
    Route::patch('/orders/{id}', [RiderController::class, 'updateOrderStatus']);
    Route::get('/earnings', [RiderController::class, 'earnings']);
});

Route::prefix('customer')
    ->middleware(['auth:api', 'role:customer'])
    ->group(function () {
        // Restaurants
        Route::get('/restaurants', [CustomerController::class, 'restaurants']);
        Route::get('/restaurants/{id}/menu', [CustomerController::class, 'menu']);

        // Orders
        Route::post('/orders', [CustomerController::class, 'placeOrder']);
        Route::get('/orders/{id}', [CustomerController::class, 'trackOrder']);

        // Reviews
        Route::post('/reviews', [CustomerController::class, 'review']);
        Route::put('/reviews/{id}', [CustomerController::class, 'updateReview']);
        Route::delete('/reviews/{id}', [CustomerController::class, 'deleteReview']);

        // Profile
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);

        // Addresses
        Route::get('/addresses', [CustomerController::class, 'listAddresses']);
        Route::post('/addresses', [CustomerController::class, 'createAddress']);
        Route::put('/addresses/{id}', [CustomerController::class, 'updateAddress']);
        Route::delete('/addresses/{id}', [CustomerController::class, 'deleteAddress']);
    });


Route::get('errors', [ErrorController::class, 'index']);

 