<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogCtrl;
use App\Http\Controllers\ContactUsCtrl;
use App\Http\Controllers\AboutUsCtrl;
use App\Http\Controllers\ProductCtrl;
use App\Http\Controllers\UserCtrl;

Route::controller(AuthController::class)->group(function () {
    Route::put('login', 'login');
    Route::post('register', 'register');
    Route::put('logout', 'logout');
    Route::put('refresh', 'refresh');
});

Route::controller(\App\Http\Controllers\CategoryCtrl::class)->group(function () {
    Route::get('/category', 'index');
    Route::get('/category/product', 'getCategoryWithProduct');
    Route::get('/category/{id}', 'getCategoryWithProduct')->where(['id' => '[0-9]+']);
});

Route::middleware(['jwt.auth'])->group(function()
{
    Route::get('me',[AuthController::class,'me']);
    Route::controller(UserCtrl::class)->group(function()
    {
        Route::get('/user','index');
        Route::post('/user/store','store');
        Route::get('/user/show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('/user/update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('/user/destroy','destroy');
    });
    Route::controller(BlogCtrl::class)->group(function()
    {
        Route::get('/blog','index');
        Route::post('/blog/store','store');
        Route::get('/blog/show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('/blog/update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('/blog/destroy/{id}','destroy')->where(['id'=>'[0-9]+']);
    });
    Route::controller(ProductCtrl::class)->group(function()
    {
        Route::get('/product','index');
        Route::post('/product/store','store');
        Route::get('/product/show/{id}','show')->where(['id'=>'[0-9]+']);
        Route::put('/product/update/{id}','update')->where(['id'=>'[0-9]+']);
        Route::delete('/product/destroy/{id}','destroy')->where(['id'=>'[0-9]+']);
    });
    Route::controller(ContactUsCtrl::class)->group(function(){
        Route::get('/contact','index');
        Route::put('/contact/update/{id}','update')->where(['id'=>'[0-9]+']);
    });
    Route::controller(AboutUsCtrl::class)->group(function(){
        Route::get('/about}','index');
        Route::put('/about/update/{id}','update')->where(['id'=>'[0-9]+']);
    });


});