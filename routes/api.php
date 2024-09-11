<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DetailUserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Register and Login User
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('activate-account', [AuthController::class, 'activateAccount']);
    Route::post('resend-activation-code', [AuthController::class, 'resendActivationCode']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('users')->group(function () {
        Route::post('detail-user', [DetailUserController::class, 'addOrUpdateDetailUserInfo']);
        Route::post('get-detail-user', [DetailUserController::class, 'getDetailUser']);
        Route::get('all-users', [DetailUserController::class, 'getAllUsers']);
        Route::get('info', [DetailUserController::class, 'getUser']);
    });

    Route::prefix('groups')->group(function () {
        Route::post('/', [GroupController::class, 'createGroup']);
        Route::get('/', [GroupController::class, 'getGroupsByUserLogin']);
        Route::post('/adding-user', [GroupController::class, 'addUserToGroupMemberByUsername']);
        Route::post('/remove-user', [GroupController::class, 'removeUserFromGroupMember']);
        Route::post('/detail', [GroupController::class, 'getDetailGroup']);
    });

    Route::prefix('trips')->group(function () {
        Route::post('/add-trip', [TripController::class, 'addTrip']);
        Route::post('/', [TripController::class, 'getAllTrips']);
        Route::post('/detail', [TripController::class, 'getTripByToken']);
        Route::post('/change-status', [TripController::class, 'changeTripStatus']);
        Route::post('/delete-trip', [TripController::class, 'deleteTrip']);

        Route::post('/add-trip-monitoring', [TripController::class, 'addTripMonitoring']);
        Route::post('/add-face-monitoring', [TripController::class, 'addFaceMonitoring']);

        Route::get('/monitoring-trip', [TripController::class, 'getTripMonitoringSSE']);
        Route::get('/monitoring-face', [TripController::class, 'getFaceMonitoringSSE']);
    });
});

Route::get('/', function () {
    return 'Hello dunia';
});
