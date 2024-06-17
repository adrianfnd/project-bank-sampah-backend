<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\HistoryController;

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

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register-verification', [AuthController::class, 'registerVerification']);
Route::post('/login', [AuthController::class, 'login']);

// Forgot Password
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/update-password', [UserController::class, 'updatePassword']);

    // Home
    Route::get('/waste-collections', [HomeController::class, 'index']);

    // Products
    Route::get('/list-products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // History
    Route::get('/waste-collection-history', [HistoryController::class, 'wasteCollectionHistory']);
    Route::get('/point-redemption-history', [HistoryController::class, 'pointRedemptionHistory']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});


