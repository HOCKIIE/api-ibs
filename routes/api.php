<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserCtrl;
use App\Http\Controllers\BlogCtrl;
use App\Http\Controllers\ContactUsCtrl;
use App\Http\Controllers\AboutUsCtrl;
use App\Http\Controllers\ProductCtrl;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::middleware(['api'])->group(function()
{
    Route::get('me',[AuthController::class,'me']);
    Route::controller('users',UserCtrl::class)->group(function()
    {
        Route::get('','index');
        Route::post('store','store');
        Route::get('show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('destroy/{id}','destroy')->where(['id'=>'[0-9]+']);
    });
    Route::controller('blog',BlogCtrl::class)->group(function()
    {
        Route::get('/','index');
        Route::post('store','store');
        Route::get('show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('destroy/{id}','destroy')->where(['id'=>'[0-9]+']);
    });
    Route::controller('product',ProductCtrl::class)->group(function()
    {
        Route::get('/','index');
        Route::post('store','store');
        Route::get('show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('destroy/{id}','destroy')->where(['id'=>'[0-9]+']);
    });
    Route::controller('contact',ContactUsCtrl::class)->group(function(){
        Route::put('update/{id}','update')->where(['id'=>'[0-9]+']);
    });
    Route::controller('about',AboutUsCtrl::class)->group(function(){
        Route::put('update/{id}','update')->where(['id'=>'[0-9]+']);
    });

});