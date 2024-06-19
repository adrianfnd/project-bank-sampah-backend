<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\HistoryController;
use App\Http\Controllers\API\ProductExchangeController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\NasabahController;

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
Route::post('/register-resend-otp', [AuthController::class, 'registerResendOtp']);
Route::post('/register-verification', [AuthController::class, 'registerVerification']);
Route::post('/login', [AuthController::class, 'login']);

// Forgot Password
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// User
Route::middleware('auth:sanctum')->group(function () {
    // Profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/update-password', [UserController::class, 'updatePassword']);

    // Home
    Route::get('/waste-collections', [HomeController::class, 'wasteCollection']);
    Route::get('/waste-banks', [HomeController::class, 'wasteBank']);

    // Products
    Route::get('/list-products', [ProductController::class, 'index']);

    // History
    Route::get('/waste-collection-history', [HistoryController::class, 'wasteCollectionHistoryCostumer']);
    Route::get('/point-redemption-history', [HistoryController::class, 'pointRedemptionHistoryCostumer']);

    // Pickup Request
    Route::post('/pickup-requests', [TransactionController::class, 'createPickupRequest']);

    // Product Exchange
    Route::post('/product-exchange', [ProductExchangeController::class, 'exchangeProduct']);

    // Waste Collection
    Route::post('/waste-collections', [WasteCollectionController::class, 'createWasteCollection']);

    // Notification
    Route::get('/list-notifications', [NotificationController::class, 'getCostomerNotifications']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->prefix('staff')->group(function () {
    // Products
    Route::get('/list-products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Waste Collection
    Route::get('/waste-collections', [WasteCollectionController::class, 'index']);
    Route::put('/waste-collections/{id}/confirm', [WasteCollectionController::class, 'confirmWasteCollection']);
    Route::post('/waste-collections/{id}/submit', [WasteCollectionController::class, 'submitWasteCollection']);

     // History
     Route::get('/waste-collection-history', [HistoryController::class, 'wasteCollectionHistoryStaff']);
     Route::get('/point-redemption-history', [HistoryController::class, 'pointRedemptionHistoryStaff']);

    // Nasabah
    Route::get('/list-nasabah', [NasabahController::class, 'index']);
    Route::post('/create-nasabah', [NasabahController::class, 'store']);

    // Notification
    Route::get('/list-notifications', [NotificationController::class, 'getStaffNotifications']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});



