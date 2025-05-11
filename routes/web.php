<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/index', [App\Http\Controllers\IndexController::class, 'index']);
Route::get('/demo-query', [App\Http\Controllers\DemoController::class, 'index']);
Route::get('/list-job/{editorId}', [App\Http\Controllers\DemoController::class, 'listJob']);
Route::get('/sort-user-file', [App\Http\Controllers\DemoController::class, 'sortUserFile']);
Route::get('/eloquent', [App\Http\Controllers\DemoController::class, 'eloquent']);
Route::get('/login', [App\Http\Controllers\AuthController::class, 'getLogin']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'postLogin']);
Route::group(['middleware' => 'checkLogin'], function () {
    //Route sau khi login moi truy cap ddc
    Route::get('dashboard', function () {
        echo "Dashboard";
    }) ;
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index']);
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create']);
        Route::post('/store', [App\Http\Controllers\UserController::class, 'store']);
        Route::get('/show/{id}', [App\Http\Controllers\UserController::class, 'show']);
        Route::post('/update/{id}', [App\Http\Controllers\UserController::class, 'update']);
        Route::post('/single-upload/{id}', [App\Http\Controllers\UserController::class, 'singleUpload']);
        Route::get('/show/confirm/{id}', [App\Http\Controllers\UserController::class, 'confirm']);
        Route::get('/multiple-upload/{userId}', [App\Http\Controllers\UserController::class, 'multipleUpload']);
        Route::post('/multiple-upload/upload', [App\Http\Controllers\UserController::class, 'executeUpload'] );
        Route::get('synchronize/{id}',[App\Http\Controllers\UserController::class, 'synchronize']);
    });
    Route::group(['prefix' => 'statistic'], function () {
        Route::get('/',[App\Http\Controllers\StatisticController::class, 'index']);
        Route::get('/salary/paid/{id}',[App\Http\Controllers\StatisticController::class, 'paid']);
        Route::get('/export',[App\Http\Controllers\StatisticController::class, 'export']);
    });

    Route::group(['prefix' => 'editors'], function () {
        Route::get('/',[App\Http\Controllers\EditorController::class, 'index']);
        Route::post('/edit/{id}',[App\Http\Controllers\EditorController::class, 'update']);
        Route::get('/download/{id}',[App\Http\Controllers\EditorController::class, 'download']);
    });
    Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout']);

});