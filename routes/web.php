<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/health', [HealthController::class, 'index']);

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Resident Management (T12)
Route::get('/residents', [ResidentController::class, 'index'])
    ->name('residents.index');

Route::get('/residents/create', [ResidentController::class, 'create'])
    ->name('residents.create');

Route::post('/residents', [ResidentController::class, 'store'])
    ->name('residents.store');

Route::get('/residents/{id}/edit', [ResidentController::class, 'edit'])
    ->name('residents.edit');

Route::put('/residents/{id}', [ResidentController::class, 'update'])
    ->name('residents.update');

Route::patch('/residents/{id}/deactivate', [ResidentController::class, 'deactivate'])
    ->name('residents.deactivate');

// Service Request Management (T13)
Route::get('/service-requests', [ServiceRequestController::class, 'index'])
    ->name('service-requests.index');

Route::get('/service-requests/create', [ServiceRequestController::class, 'create'])
    ->name('service-requests.create');

Route::post('/service-requests', [ServiceRequestController::class, 'store'])
    ->name('service-requests.store');

Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show'])
    ->name('service-requests.show');

Route::post('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus'])
    ->name('service-requests.status');
