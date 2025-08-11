<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingPdfController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Api\SpaceCategoryController;

use App\Http\Controllers\CameraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SpaceSubCategoryController;
use App\Http\Controllers\Api\EcoTrailMainSpaceController;


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







Route::post('/adminsignup', [AuthController::class, 'adminsignup']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);



// ===================s======================//
// ->middleware('permission') //for admin routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('space-categories', SpaceCategoryController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

     Route::any('/all-users', [AuthController::class, 'all_users']);

    Route::prefix('space-sub-categories')->group(function () {
        Route::get('/', [SpaceSubCategoryController::class, 'index']);
        Route::get('/all-cat', [SpaceSubCategoryController::class, 'all_cat']);
        Route::post('/', [SpaceSubCategoryController::class, 'store']);
        Route::get('{id}', [SpaceSubCategoryController::class, 'show']);
        Route::put('{id}', [SpaceSubCategoryController::class, 'update']);
        Route::get('{id}', [SpaceSubCategoryController::class, 'destroy']);
    });


    Route::prefix('eco-trail')->group(function () {
        Route::get('/main-spaces', [EcoTrailMainSpaceController::class, 'index']);
        Route::post('/main-spaces', [EcoTrailMainSpaceController::class, 'store']);
        Route::get('/main-spaces/{id}', [EcoTrailMainSpaceController::class, 'show']);
        Route::put('/main-spaces/{id}', [EcoTrailMainSpaceController::class, 'update']);
        Route::get('/main-spaces/delete/{id}', [EcoTrailMainSpaceController::class, 'destroy']);

        Route::get('/main-spaces-map-track/{id}', [EcoTrailMainSpaceController::class, 'map_markers']);
    });



    Route::get('/get-all-latlong', [EcoTrailMainSpaceController::class, 'getAllLatLong']);
     Route::get('/get-all-count', [EcoTrailMainSpaceController::class, 'getAllCount']);



});



    Route::get('/guest-all-cat', [SpaceSubCategoryController::class, 'all_cat']);
    Route::apiResource('guest-space-categories', SpaceCategoryController::class);
    Route::prefix('guest-eco-trail')->group(function () {
        Route::get('/main-spaces', [EcoTrailMainSpaceController::class, 'index']);
        Route::get('/main-spaces/{id}', [EcoTrailMainSpaceController::class, 'show']);
        Route::put('/main-spaces/{id}', [EcoTrailMainSpaceController::class, 'update']);
        Route::get('/main-spaces/delete/{id}', [EcoTrailMainSpaceController::class, 'destroy']);

        Route::get('/main-spaces-map-track/{id}', [EcoTrailMainSpaceController::class, 'map_markers']);
    });



    Route::get('/get-all-latlong', [EcoTrailMainSpaceController::class, 'getAllLatLong']);
     Route::get('/get-all-count', [EcoTrailMainSpaceController::class, 'getAllCount']);




