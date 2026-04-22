<?php

use App\Http\Controllers\Api\GymCustomerController;
use App\Http\Controllers\Api\GymServicesController;
use Illuminate\Support\Facades\Route;

Route::get('/gym-services', [GymServicesController::class, 'index']);
Route::get('/gym-register-customer', [GymCustomerController::class, 'register']);

