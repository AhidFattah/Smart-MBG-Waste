<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\FoodWasteController;
use App\Http\Controllers\DashboardController;

// Rute Publik & Autentikasi
Route::get('/', function () { 
    return redirect()->route('login'); 
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.auth');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Terproteksi (Harus Login)
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Aktor: Admin Pusat
    Route::middleware(['role:admin_pusat'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/schools', [AdminController::class, 'manageSchools'])->name('schools.index');
        Route::post('/schools', [AdminController::class, 'storeSchool'])->name('schools.store');
        Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
    });

    // Aktor: Petugas Dapur (Hulu)
    Route::middleware(['role:petugas_dapur'])->prefix('dapur')->name('dapur.')->group(function () {
        Route::get('/distribution/create', [DapurController::class, 'createDistribution'])->name('distribution.create');
        Route::post('/distribution', [DapurController::class, 'storeDistribution'])->name('distribution.store');
        Route::get('/menus', [DapurController::class, 'manageMenus'])->name('menus.index');
        Route::post('/menus', [DapurController::class, 'storeMenu'])->name('menus.store');
    });

    // Aktor: Petugas Sekolah (Hilir)
    Route::middleware(['role:petugas_sekolah'])->prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/waste/input/{distribution_id}', [FoodWasteController::class, 'createWasteLog'])->name('waste.input');
        Route::post('/waste/store/{distribution_id}', [FoodWasteController::class, 'storeWasteLog'])->name('waste.store');
    });

    // Aktor Bersama: Kepala Sekolah & Admin Pusat
    Route::middleware(['role:kepala_sekolah,admin_pusat'])->prefix('analitik')->name('analitik.')->group(function () {
        Route::get('/trend', [DashboardController::class, 'showTrends'])->name('trend');
    });
});