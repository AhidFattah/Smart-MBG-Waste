<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\FoodWasteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BackupRestoreController;

// ==========================================
// RUTE PUBLIK & AUTENTIKASI
// ==========================================
Route::get('/', function () { 
    return redirect()->route('login'); 
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.auth');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Simulasi Password Reset & OTP
Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('password.forgot');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify-otp');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Utility System Switcher
Route::get('/lang/{lang}', [AuthController::class, 'setLocale'])->name('lang.switch');
Route::post('/toggle-theme', [AuthController::class, 'toggleTheme'])->name('theme.toggle');

// ==========================================
// RUTE TERPROTEKSI (HARUS LOGIN)
// ==========================================
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/save-widgets', [DashboardController::class, 'saveWidgets'])->name('dashboard.save-widgets');
    Route::get('/api/notifications', [DashboardController::class, 'getNotifications'])->name('api.notifications');
    Route::post('/api/notifications/read', [DashboardController::class, 'markNotificationsRead'])->name('api.notifications.read');

    // ----------------------------------------------------
    // SUPER ADMIN (admin_pusat)
    // ----------------------------------------------------
    Route::middleware(['role:admin_pusat'])->prefix('admin')->name('admin.')->group(function () {
        // CRUD User
        Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
        Route::post('/users/store', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/update/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/delete/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        // CRUD Sekolah
        Route::get('/schools', [AdminController::class, 'manageSchools'])->name('schools.index');
        Route::post('/schools/store', [AdminController::class, 'storeSchool'])->name('schools.store');
        Route::put('/schools/update/{id}', [AdminController::class, 'updateSchool'])->name('schools.update');
        Route::delete('/schools/delete/{id}', [AdminController::class, 'destroySchool'])->name('schools.destroy');

        // Audit Logs (Activity Logs)
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs.index');

        // System Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
        Route::post('/settings/update', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Backup & Restore Database
        Route::get('/backup', [BackupRestoreController::class, 'index'])->name('backup.index');
        Route::get('/backup/run', [BackupRestoreController::class, 'runBackup'])->name('backup.run');
        Route::post('/backup/restore', [BackupRestoreController::class, 'runRestore'])->name('backup.restore');
        Route::delete('/backup/delete/{file}', [BackupRestoreController::class, 'deleteBackup'])->name('backup.delete');
        Route::get('/backup/download/{file}', [BackupRestoreController::class, 'downloadBackup'])->name('backup.download');
    });

    // ----------------------------------------------------
    // ADMIN DAPUR (petugas_dapur) & SHARED INVENTORY
    // ----------------------------------------------------
    Route::middleware(['role:admin_pusat,petugas_dapur'])->prefix('dapur')->name('dapur.')->group(function () {
        // CRUD Inventaris
        Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
        Route::post('/inventaris/store', [InventarisController::class, 'store'])->name('inventaris.store');
        Route::put('/inventaris/update/{id}', [InventarisController::class, 'update'])->name('inventaris.update');
        Route::delete('/inventaris/delete/{id}', [InventarisController::class, 'destroy'])->name('inventaris.destroy');
        Route::post('/inventaris/import', [InventarisController::class, 'importExcel'])->name('inventaris.import');
        Route::get('/inventaris/export-excel', [InventarisController::class, 'exportExcel'])->name('inventaris.export-excel');
        Route::get('/inventaris/export-pdf', [InventarisController::class, 'exportPdf'])->name('inventaris.export-pdf');

        // CRUD Supplier
        Route::get('/suppliers', [InventarisController::class, 'indexSuppliers'])->name('suppliers.index');
        Route::post('/suppliers/store', [InventarisController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/update/{id}', [InventarisController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/delete/{id}', [InventarisController::class, 'destroySupplier'])->name('suppliers.destroy');

        // CRUD Menu
        Route::get('/menus', [AdminController::class, 'manageMenus'])->name('menus.index');
        Route::post('/menus/store', [AdminController::class, 'storeMenu'])->name('menus.store');
        Route::put('/menus/update/{id}', [AdminController::class, 'updateMenu'])->name('menus.update');
        Route::delete('/menus/delete/{id}', [AdminController::class, 'destroyMenu'])->name('menus.destroy');

        // CRUD & Otomatisasi Distribusi
        Route::get('/distributions', [DistributionController::class, 'index'])->name('distributions.index');
        Route::post('/distributions/store', [DistributionController::class, 'store'])->name('distributions.store');
        Route::put('/distributions/update/{id}', [DistributionController::class, 'update'])->name('distributions.update');
        Route::delete('/distributions/delete/{id}', [DistributionController::class, 'destroy'])->name('distributions.destroy');
        Route::get('/distributions/surat-jalan/{id}', [DistributionController::class, 'printSuratJalan'])->name('distributions.surat-jalan');
        Route::post('/distributions/update-status/{id}', [DistributionController::class, 'updateStatus'])->name('distributions.update-status');
    });

    // ----------------------------------------------------
    // PETUGAS SEKOLAH (petugas_sekolah)
    // ----------------------------------------------------
    Route::middleware(['role:petugas_sekolah,admin_pusat'])->prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/waste/input', [FoodWasteController::class, 'showInputForm'])->name('waste.input');
        Route::post('/waste/store', [FoodWasteController::class, 'storeWasteLog'])->name('waste.store');
        Route::get('/waste/riwayat', [FoodWasteController::class, 'riwayatWaste'])->name('waste.riwayat');
    });

    // ----------------------------------------------------
    // SHARED / VIEWER REPORTS & ANALYTICS (Semua Role Bisa)
    // ----------------------------------------------------
    Route::get('/laporan', [FoodWasteController::class, 'showLaporan'])->name('laporan.index');
    Route::get('/laporan/export-excel', [FoodWasteController::class, 'exportExcel'])->name('laporan.export-excel');
    Route::get('/laporan/export-pdf', [FoodWasteController::class, 'exportPdf'])->name('laporan.export-pdf');
    
    Route::get('/analitik', [DashboardController::class, 'showTrends'])->name('analitik.index');

    // Fallbacks
    Route::get('/inventaris', function () {
        return redirect()->route('dapur.inventaris.index');
    });
    Route::get('/sekolah/waste-input', function () {
        return redirect()->route('sekolah.waste.input');
    });
});