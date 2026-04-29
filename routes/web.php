<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/activity', [ProfileController::class, 'activity'])->name('profile.activity');

    // Device Management (Full CRUD)
    Route::resource('devices', DeviceController::class);
    Route::post('/devices/{device}/ping', [DeviceController::class, 'ping'])->name('devices.ping');
    Route::get('/devices/{device}/export', [DeviceController::class, 'export'])->name('devices.export');
    Route::get('/devices-export', [DeviceController::class, 'exportAll'])->name('devices.export-all');

    // Port Management (Nested under devices)
    Route::prefix('devices/{device}/ports')->name('devices.ports.')->group(function () {
        Route::get('/', [PortController::class, 'index'])->name('index');
        Route::get('/{port}', [PortController::class, 'show'])->name('show');
        Route::get('/{port}/edit', [PortController::class, 'edit'])->name('edit');
        Route::put('/{port}', [PortController::class, 'update'])->name('update');
        Route::post('/bulk-update', [PortController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/export', [PortController::class, 'export'])->name('export');
    });

    // Location Management (Full CRUD)
    Route::resource('locations', LocationController::class);
    Route::get('/locations/tree', [LocationController::class, 'tree'])->name('locations.tree');
    Route::get('/locations/{location}/devices', [LocationController::class, 'devices'])->name('locations.devices');
    Route::get('/locations-export', [LocationController::class, 'export'])->name('locations.export');

    // Alerts Management
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.read');
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::post('/alerts/bulk-resolve', [AlertController::class, 'bulkResolve'])->name('alerts.bulk-resolve');
    Route::get('/alerts/count/unread', [AlertController::class, 'getUnreadCount'])->name('alerts.unread-count');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/expiry', [ReportController::class, 'expiry'])->name('expiry');
        Route::get('/port-usage', [ReportController::class, 'portUsage'])->name('port-usage');
        Route::get('/availability', [ReportController::class, 'availability'])->name('availability');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // Monitoring
    Route::get('/monitoring/logs', [MonitoringController::class, 'logs'])->name('monitoring.logs');
    Route::get('/monitoring/stats', [MonitoringController::class, 'stats'])->name('monitoring.stats');
    Route::post('/monitoring/run', [MonitoringController::class, 'runMonitoring'])->name('monitoring.run');
    Route::get('/monitoring/export', [MonitoringController::class, 'export'])->name('monitoring.export');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/system-info', [SettingsController::class, 'systemInfo'])->name('settings.system-info');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
});

