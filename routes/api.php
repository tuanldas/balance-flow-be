<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryIconController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes (không cần authentication)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
});

// Protected Routes (cần authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
    });

    // Category Icons Routes
    Route::get('/category-icons', [CategoryIconController::class, 'index']);

    // Categories Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::patch('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::get('/{id}/subcategories', [CategoryController::class, 'subcategories']);
    });

    // Accounts Routes
    Route::prefix('accounts')->group(function () {
        Route::get('/balance/total', [AccountController::class, 'totalBalance']);
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/{id}', [AccountController::class, 'show']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::patch('/{id}', [AccountController::class, 'update']);
        Route::delete('/{id}', [AccountController::class, 'destroy']);
        Route::post('/{id}/toggle-active', [AccountController::class, 'toggleActive']);
    });

    // Transactions Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/summary', [TransactionController::class, 'summary']);
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::patch('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });
});
