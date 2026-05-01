<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SNMPController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MonitoringController;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Devices
    Route::post('/devices/{device}/ping', [DeviceController::class, 'ping']);
    Route::get('/devices/{device}/ports', [DeviceController::class, 'ports']);

    // Locations
    Route::get('/locations/{location}/devices', [LocationController::class, 'devices']);

    // Alerts
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);
    Route::get('/alerts/count/unread', [AlertController::class, 'unreadCount']);

// });

// SNMP Monitoring
// Route::middleware('auth:sanctum')->group(function () {
    
    // SNMP Monitoring API
    Route::prefix('snmp')->name('snmp.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [SNMPController::class, 'dashboard'])
            ->name('api.dashboard');
        
        // Devices list
        Route::get('/devices', [SNMPController::class, 'devices'])
            ->name('devices');
        
        // Device specific routes
        Route::prefix('devices/{device}')->group(function () {
            
            // Performance metrics
            Route::get('/performance', [SNMPController::class, 'performance'])
                ->name('performance');
            
            // Interface statistics
            Route::get('/interfaces', [SNMPController::class, 'interfaces'])
                ->name('interfaces');
            
            // System information
            Route::get('/system', [SNMPController::class, 'systemInfo'])
                ->name('system');
            
            // Test connection
            Route::post('/test', [SNMPController::class, 'testConnection'])
                ->name('test');
            
            // Walk OID
            Route::post('/walk', [SNMPController::class, 'walkOID'])
                ->name('walk');
            
            // Set value
            Route::post('/set', [SNMPController::class, 'setValue'])
                ->name('set');
        });
        
        // Discovery
        Route::post('/discover', [SNMPController::class, 'discoverDevices'])
            ->name('discover');
        
        // Run monitoring
        Route::post('/monitoring/run', [SNMPController::class, 'runMonitoring'])
            ->name('monitoring.run');
     });
    //  });