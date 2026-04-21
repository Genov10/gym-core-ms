<?php

use App\Http\Controllers\Api\GymServicesController;
use Illuminate\Support\Facades\Route;

Route::get('/gym-services', [GymServicesController::class, 'index']);

