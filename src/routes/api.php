<?php

use App\Http\Controllers\Api\GymCustomerController;
use App\Http\Controllers\Api\GymServicesController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PassExpiryController;
use App\Http\Controllers\Api\SubscriptionExtendController;
use App\Http\Controllers\Api\SubscriptionFreezeController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\WayForPayController;
use App\Http\Controllers\PaymentResultController;
use Illuminate\Support\Facades\Route;

Route::get('/gym-services', [GymServicesController::class, 'index']);
Route::get('/gym-register-customer', [GymCustomerController::class, 'register']);
Route::get('/gym-order-create', [OrderController::class, 'create']);
Route::get('/gym-start-visit', [VisitController::class, 'startVisit']);
Route::get('/gym-finish-visit', [VisitController::class, 'finishVisit']);
Route::get('/gym-get-customer-gym-services', [GymCustomerController::class, 'getCustomerGymServices']);
Route::get('/gym-get-customer-gym-service-info', [GymCustomerController::class, 'getCustomerGymServiceInfo']);
Route::get('/gym-get-customer-gym-service-extend', [SubscriptionExtendController::class, 'create']);
Route::get('/finish-forgotten-visits', [VisitController::class, 'finishForgottenVisit']);
Route::get('/finish-visits-periodically', [VisitController::class, 'finishVisitsPeriodically']);

Route::get('/check-passes-for-one-day', [PassExpiryController::class, 'checkPassesForOneDay']);
Route::get('/check-passes-for-three-days', [PassExpiryController::class, 'checkPassesForThreeDays']);
Route::get('/gym-freeze-preview', [SubscriptionFreezeController::class, 'preview']);
Route::get('/gym-freeze-confirm', [SubscriptionFreezeController::class, 'confirm']);
Route::get('/check-freezes-expiry', [SubscriptionFreezeController::class, 'finishExpired']);

// WayForPay
Route::post('/wayforpay/purchase', [WayForPayController::class, 'purchase']);
Route::post('/wayforpay/callback', [WayForPayController::class, 'callback']);
Route::post('/wayforpay/extend-confirm', [SubscriptionExtendController::class, 'confirm']);
Route::match(['get', 'post'], '/wayforpay/return', PaymentResultController::class);
