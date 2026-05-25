<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BroadcastsController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\GymServicesController;
use App\Http\Controllers\Admin\RoomsController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\VisitsController;
use App\Http\Controllers\PaymentResultController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/terms', TermsController::class)->name('terms');

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
        Route::get('/services/{service}', [GymServicesController::class, 'show'])->name('admin.services.show');
        Route::put('/services/{service}', [GymServicesController::class, 'update'])->name('admin.services.update');
        Route::post('/services/{service}/deactivate', [GymServicesController::class, 'deactivate'])->name('admin.services.deactivate');

        Route::get('/customers', [CustomersController::class, 'index'])->name('admin.customers.index');
        Route::get('/customers/{customer}', [CustomersController::class, 'show'])->name('admin.customers.show');
        Route::post('/customers/{customer}/toggle-ban', [CustomersController::class, 'toggleBan'])->name('admin.customers.toggle-ban');
        Route::put('/customers/{customer}/flags', [CustomersController::class, 'updateFlags'])->name('admin.customers.update-flags');
        Route::post('/customers/{customer}/subscriptions/{subscription}/freeze', [CustomersController::class, 'freezeSubscription'])->name('admin.customers.subscriptions.freeze');

        Route::get('/sales', [SalesController::class, 'index'])->name('admin.sales.index');

        Route::get('/visits', [VisitsController::class, 'index'])->name('admin.visits.index');

        Route::get('/broadcasts', [BroadcastsController::class, 'index'])->name('admin.broadcasts.index');
        Route::post('/broadcasts', [BroadcastsController::class, 'send'])->name('admin.broadcasts.send');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});
