
<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResidentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/health', [HealthController::class, 'index']);

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
