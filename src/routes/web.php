<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\GymServicesController;
use App\Http\Controllers\Admin\RoomsController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\VisitsController;
use App\Http\Controllers\PaymentResultController;
use Illuminate\Support\Facades\Route;

// WayForPay редирект після оплати (POST без CSRF — див. bootstrap/app.php)
Route::match(['get', 'post'], '/result', PaymentResultController::class)->name('payment.result');

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
        Route::get('/rooms/{room}', [RoomsController::class, 'show'])->name('admin.rooms.show');
        Route::put('/rooms/{room}', [RoomsController::class, 'update'])->name('admin.rooms.update');

        Route::get('/services', [GymServicesController::class, 'index'])->name('admin.services.index');
        Route::post('/services', [GymServicesController::class, 'store'])->name('admin.services.store');
        Route::delete('/services/{service}', [GymServicesController::class, 'destroy'])->name('admin.services.destroy');

        Route::get('/customers', [CustomersController::class, 'index'])->name('admin.customers.index');
        Route::get('/customers/{customer}', [CustomersController::class, 'show'])->name('admin.customers.show');
        Route::post('/customers/{customer}/toggle-ban', [CustomersController::class, 'toggleBan'])->name('admin.customers.toggle-ban');

        Route::get('/sales', [SalesController::class, 'index'])->name('admin.sales.index');

        Route::get('/visits', [VisitsController::class, 'index'])->name('admin.visits.index');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});
