<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MonitoringController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
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

    // Monitoring
    Route::get('/monitoring/logs', [MonitoringController::class, 'logs']);
    Route::post('/monitoring/run', [MonitoringController::class, 'runMonitoring']);
    Route::get('/monitoring/stats', [MonitoringController::class, 'stats']);
});
