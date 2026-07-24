<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PT TRS
|--------------------------------------------------------------------------
|
| Pembagian akses by role:
|
|   auth:sanctum group     → semua user login (owner + finance)
|     - GET semua resource (lihat data)
|     - Customer CRUD (kecuali delete) — finance manage customer baru
|     - Transaction CRUD (kecuali delete) — daily ops finance
|     - Dashboard, Analytics, Financial Report
|
|   role:owner group       → hanya owner
|     - User management (Create/Read/Update/Delete user)
|     - Master Product Create/Update/Delete (finance hanya bisa GET)
|     - Master ProductType Create/Update/Delete (finance hanya bisa GET)
|     - DELETE customer, DELETE transaction (irreversible — owner approve)
|
*/

// ─── Public routes (no auth) ────────────────────────────────────────────
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ─── Authenticated user (owner + finance) ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // PROFILE — self-service: setiap user manage profil sendiri
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::put('profile/password', [ProfileController::class, 'changePassword']);

    // CUSTOMER — finance bisa CRUD kecuali delete
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        // /dropdown HARUS sebelum /{id} supaya tidak ke-catch dynamic route
        Route::get('/dropdown', [CustomerController::class, 'dropdown']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        // DELETE pindah ke role:owner group di bawah
    });

    // PRODUCT TYPE — ffinance bisa CRUD kecuali delete
    Route::prefix('product-types')->group(function () {
        Route::get('/', [ProductTypeController::class, 'index']);
        Route::get('/{id}', [ProductTypeController::class, 'show']);
        Route::post('/', [ProductTypeController::class, 'store']);
        Route::put('/{id}', [ProductTypeController::class, 'update']);
        // DELETE pindah ke role:owner di bawah
    });

    // EXPEDITION — finance bisa CRUD kecuali delete
    // Delete = owner only
    Route::prefix('expeditions')->group(function () {
        Route::get('/', [ExpeditionController::class, 'index']);
        Route::post('/', [ExpeditionController::class, 'store']);
        Route::put('/{id}', [ExpeditionController::class, 'update']);
        // /dropdown HARUS sebelum /{id} supaya tidak ke-catch dynamic route
        Route::get('/dropdown', [ExpeditionController::class, 'dropdown']);
        Route::get('/{id}', [ExpeditionController::class, 'show']);
        // DELETE pindah ke role:owner di bawah
    });

    // PRODUCT — finance hanya READ. Create/Update/Delete = owner only
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        // POST/PUT/DELETE pindah ke role:owner di bawah
    });

    // SALES TRANSACTIONS — finance CRUD kecuali delete + status update
    Route::prefix('sales-transactions')->group(function () {
        Route::get('/', [SalesTransactionController::class, 'index']);
        // GET history HARUS di-define sebelum {id} supaya tidak ke-catch route /{id}
        Route::get('/history', [SalesTransactionController::class, 'history']);
        Route::post('/', [SalesTransactionController::class, 'store']);
        Route::get('/{id}', [SalesTransactionController::class, 'show']);
        Route::put('/{id}', [SalesTransactionController::class, 'update']);
        Route::patch('/{id}/status', [SalesTransactionController::class, 'updateStatus']);
        // DELETE pindah ke role:owner di bawah
    });

    // DASHBOARD + ANALYTICS + FINANCIAL REPORT — finance & owner sama-sama bisa
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('financial', [DashboardController::class, 'financialReport']);
    });

    Route::prefix('analytics')->group(function () {
        Route::get('monthly-sales', [AnalyticsController::class, 'monthlySales']);
        Route::get('monthly-profit', [AnalyticsController::class, 'monthlyProfit']);
        Route::get('top-products', [AnalyticsController::class, 'topProducts']);
    });
});

// ─── Owner only — sensitive operations ──────────────────────────────────
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {

    // USER MANAGEMENT — full CRUD owner-only
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // MASTER PRODUCT TYPE — Delete 
    Route::delete('/product-types/{id}', [ProductTypeController::class, 'destroy']);

    // MASTER PRODUCT — Create/Update/Delete 
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // MASTER EXPEDITION — Delete
    Route::delete('/expeditions/{id}', [ExpeditionController::class, 'destroy']);

    // DELETE customer & transaction — owner approval (irreversible)
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    Route::delete('/sales-transactions/{id}', [SalesTransactionController::class, 'destroy']);

    // ACTIVITY LOG — audit trail, hanya owner yang bisa lihat
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
