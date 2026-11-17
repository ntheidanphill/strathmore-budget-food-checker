<?php

/*
|--------------------------------------------------------------------------
| Web Routes - Strathmore Budget Food Checker (Group 8)
|--------------------------------------------------------------------------
| ONE routes file for the whole app. It lives in Module 0 so nobody has
| merge conflicts. Routes are grouped by WHO may use them:
|
|   1. Public (guests/students)   -> search page + JSON endpoint   (R1)
|   2. Login / logout             -> AuthController                (R2)
|   3. Vendor Manager only        -> vendor dashboard              (R3)
|   4. Admin + Vendor Manager     -> menu item CRUD                (R3, R4)
|   5. Admin only                 -> vendors, managers, lookups    (R4)
|
| Access is enforced on the SERVER by the 'auth' and 'role' middleware,
| not just by hiding links (API brief 5.6, requirement R5).
*/

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use Illuminate\Support\Facades\Route;

// ---- 1. PUBLIC: students need no account (R1) ----------------------------
Route::get('/', [SearchController::class, 'index'])->name('search');
Route::get('/api/items', [SearchController::class, 'api'])->name('api.items'); // JSON endpoint

// ---- 2. LOGIN / LOGOUT (R2) ----------------------------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---- 3. VENDOR MANAGER ONLY ----------------------------------------------
Route::middleware(['auth', 'role:vendor_manager'])->group(function () {
    Route::get('/vendor', [VendorDashboardController::class, 'index'])->name('vendor.dashboard');
});

// ---- 4. MENU ITEMS: admin AND vendor manager (controller limits vendors to their own items) ----
Route::middleware(['auth', 'role:admin,vendor_manager'])->group(function () {
    Route::resource('items', MenuItemController::class)->except(['show']);
    Route::patch('items/{item}/toggle', [MenuItemController::class, 'toggle'])->name('items.toggle');
});

// ---- 5. ADMIN ONLY (R4) --------------------------------------------------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Vendors, manager accounts, locations, categories: add / edit / DEACTIVATE (never hard delete)
    Route::resource('vendors', VendorController::class)->except(['show', 'destroy']);
    Route::patch('vendors/{vendor}/toggle', [VendorController::class, 'toggle'])->name('vendors.toggle');

    Route::resource('managers', ManagerController::class)->except(['show', 'destroy']);
    Route::patch('managers/{manager}/toggle', [ManagerController::class, 'toggle'])->name('managers.toggle');

    Route::resource('locations', LocationController::class)->only(['index', 'store', 'update']);
    Route::patch('locations/{location}/toggle', [LocationController::class, 'toggle'])->name('locations.toggle');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update']);
    Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
});
