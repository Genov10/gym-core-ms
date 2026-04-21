<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\GymServicesController;
use App\Http\Controllers\Admin\RoomsController;

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/rooms', [RoomsController::class, 'index'])->name('admin.rooms.index');
        Route::post('/rooms', [RoomsController::class, 'store'])->name('admin.rooms.store');
        Route::delete('/rooms/{room}', [RoomsController::class, 'destroy'])->name('admin.rooms.destroy');

        Route::get('/services', [GymServicesController::class, 'index'])->name('admin.services.index');
        Route::post('/services', [GymServicesController::class, 'store'])->name('admin.services.store');
        Route::delete('/services/{service}', [GymServicesController::class, 'destroy'])->name('admin.services.destroy');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});
