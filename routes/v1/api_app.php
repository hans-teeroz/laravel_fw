<?php

use App\Http\Controllers\App\AppAuthController;
use App\Http\Controllers\App\AppHomeController;
use App\Http\Controllers\UserController;
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





Route::get('/', [AppHomeController::class, 'index']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('/regitster', [AppAuthController::class, '_regitster']);
    Route::post('/login', [AppAuthController::class, '__login']);


});

Route::group(['middleware' => 'users.api'], function () {
    Route::get('me', [AppAuthController::class, '__me']);
    Route::get('refresh', [AppAuthController::class, '__refresh']);


});

Route::group(['prefix' => 'user'], function () {
    Route::post('/', [UserController::class, '__create']);
    Route::get('/', [UserController::class, '__list']);
    Route::get('/{id}', [UserController::class, '__find']);
    Route::put('/{id}', [UserController::class, '__update']);
    Route::delete('/{id}', [UserController::class, '__delete']);
});
