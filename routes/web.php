<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SNMPController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BandwidthController;
use App\Http\Controllers\MIBBrowserController;
use App\Http\Controllers\TopologyController;
use App\Http\Controllers\RackController;
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

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/activity', [ProfileController::class, 'activity'])->name('profile.activity');
    Route::post('/profile/clear-activity', [ProfileController::class, 'clearActivity'])->name('profile.clear-activity');


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
    Route::get('/alerts/notifications', [AlertController::class, 'getNotifications'])->name('alerts.notifications');

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
    
    // SNMP Monitoring
    Route::prefix('snmp')->name('snmp.')->group(function () {
        Route::get('/dashboard', [SNMPController::class, 'dashboard'])->name('dashboard');
        Route::post('/devices/{device}/test', [SNMPController::class, 'testConnection'])->name('test');
        Route::get('/devices/{device}/performance', [SNMPController::class, 'performanceView'])->name('performance');
        Route::get('/devices/{device}/interfaces', [SNMPController::class, 'interfacesView'])->name('interfaces');
        Route::post('/discover', [SNMPController::class, 'discover'])->name('discover');
    });
    
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

// Admin Audit Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/audit')->name('admin.audit.')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('index');
    Route::get('/{activity}', [AuditController::class, 'show'])->name('show');
    Route::get('/export', [AuditController::class, 'export'])->name('export');
    Route::post('/clean', [AuditController::class, 'clean'])->name('clean');
});


// API Routes for SNMP
Route::middleware(['auth'])->prefix('api/snmp')->name('api.snmp.')->group(function () {
    Route::get('/devices/{device}/performance', [SNMPController::class, 'performance'])->name('performance');
    Route::get('/devices/{device}/interfaces', [SNMPController::class, 'interfaces'])->name('interfaces');
    Route::post('/devices/{device}/test', [SNMPController::class, 'testConnection'])->name('test');
    Route::post('/discover', [SNMPController::class, 'discover'])->name('discover');
    Route::post('/monitoring/run', [SNMPController::class, 'runMonitoring'])->name('monitoring.run');
});



// Rack Visualization Routes
Route::middleware(['auth'])->prefix('racks')->name('racks.')->group(function () {
    Route::get('/', [RackController::class, 'index'])->name('index');
    Route::get('/create', [RackController::class, 'create'])->name('create');
    Route::post('/', [RackController::class, 'store'])->name('store');
    Route::get('/{rack}', [RackController::class, 'show'])->name('show');
    Route::get('/{rack}/edit', [RackController::class, 'edit'])->name('edit');
    Route::put('/{rack}', [RackController::class, 'update'])->name('update');
    Route::delete('/{rack}', [RackController::class, 'destroy'])->name('destroy');
    Route::post('/{rack}/devices/{device}/add', [RackController::class, 'addDevice'])->name('add-device');
    Route::delete('/{rack}/devices/{device}/remove', [RackController::class, 'removeDevice'])->name('remove-device');
    Route::get('/{rack}/layout', [RackController::class, 'layout'])->name('layout');
});

// Topology Routes
Route::middleware(['auth'])->prefix('topology')->name('topology.')->group(function () {
    Route::get('/', [TopologyController::class, 'index'])->name('index');
    Route::get('/data', [TopologyController::class, 'data'])->name('data');
    Route::post('/devices/{device}/discover', [TopologyController::class, 'discover'])->name('discover');
    Route::post('/discover-all', [TopologyController::class, 'discoverAll'])->name('discover-all');
});

// Bandwidth Monitoring Routes
Route::middleware(['auth'])->prefix('bandwidth')->name('bandwidth.')->group(function () {
    Route::get('/', [BandwidthController::class, 'dashboard'])->name('dashboard');
    Route::get('/devices/{device}/port', [BandwidthController::class, 'getPortBandwidth'])->name('port');
    Route::post('/devices/{device}/collect', [BandwidthController::class, 'collectNow'])->name('collect');
    Route::post('/collect-all', [BandwidthController::class, 'collectAll'])->name('collect-all');
});

// MIB Browser Routes
Route::middleware(['auth'])->prefix('mib-browser')->name('mib-browser.')->group(function () {
    Route::get('/', [MIBBrowserController::class, 'index'])->name('index');
    Route::get('/devices/{device}/get', [MIBBrowserController::class, 'get'])->name('get');
    Route::get('/devices/{device}/walk', [MIBBrowserController::class, 'walk'])->name('walk');
});


