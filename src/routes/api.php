<?php

use App\Http\Controllers\Api\GymCustomerController;
use App\Http\Controllers\Api\GymServicesController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/gym-services', [GymServicesController::class, 'index']);
Route::get('/gym-register-customer', [GymCustomerController::class, 'register']);
Route::get('/gym-order-create', [OrderController::class, 'create']);
Route::get('/gym-start-visit', [VisitController::class, 'startVisit']);

