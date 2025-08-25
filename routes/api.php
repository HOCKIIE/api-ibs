<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogCtrl;
use App\Http\Controllers\ContactUsCtrl;
use App\Http\Controllers\AboutUsCtrl;
use App\Http\Controllers\CategoryCtrl;
use App\Http\Controllers\BrandCtrl;
use App\Http\Controllers\ProductCtrl;
use App\Http\Controllers\UserCtrl;
use App\Http\Controllers\MediaCtrl;

Route::get('/',function(){
    return response()->json(['message'=>'Welcom to IBS Machinex API']);
});

Route::controller(AuthController::class)->group(function () {
    Route::put('login', 'login');
    Route::post('register', 'register');
    Route::put('logout', 'logout');
    Route::put('refresh', 'refresh');
    Route::get('me', 'me');
});

Route::controller(CategoryCtrl::class)->group(function () {
    Route::get('/category', 'getCategory');
    Route::get('/category/brand', 'getCategoryWithBrand');
    Route::get('/category/{id}', 'getCategoryById')->where(['id' => '[0-9]+']);
});
Route::controller(BrandCtrl::class)->group(function () {
    Route::get('/brand', 'getBrand');
});

Route::controller(AboutUsCtrl::class)->group(function(){
    Route::get('/about-us','index');
});

Route::controller(ContactUsCtrl::class)->group(function () {
    Route::get('/owner', 'index');
    Route::get('/sales', 'salesData');
    Route::post('/contact-us', 'store');
});

Route::get('/gallery',[MediaCtrl::class,'gallery']);
Route::put('/gallery/upload',[MediaCtrl::class,'imageUploads']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::controller(CategoryCtrl::class)->group(function () {
            Route::get('/category', 'index');
            Route::post('/category/store', 'store');
            Route::get('/category/show/{id}', 'show')->where(['id' => '[0-9]+']);
            Route::put('/category/update/{id}', 'update')->where(['id' => '[0-9]+']);
            Route::delete('/category/destroy', 'destroy');
        });
        Route::controller(BrandCtrl::class)->group(function () {
            Route::get('/brand', 'index');
            Route::post('/brand/store', 'store');
            Route::get('/brand/show/{id}', 'show')->where(['id' => '[0-9]+']);
            Route::put('/brand/update/{id}', 'update')->where(['id' => '[0-9]+']);
            Route::delete('/brand/destroy', 'destroy');
        });
        Route::controller(UserCtrl::class)->group(function () {
            Route::get('/user', 'index');
            Route::post('/user/store', 'store');
            Route::get('/user/show/{id}', 'show')->where(['id' => '[0-9]+']);
            Route::put('/user/update/{id}', 'update')->where(['id' => '[0-9]+']);
            Route::delete('/user/destroy', 'destroy');
        });
        Route::controller(BlogCtrl::class)->group(function () {
            Route::get('/blog', 'index');
            Route::post('/blog/store', 'store');
            Route::get('/blog/show/{id}', 'show')->where(['id' => '[0-9]+']);
            Route::put('/blog/update/{id}', 'update')->where(['id' => '[0-9]+']);
            Route::delete('/blog/destroy/{id}', 'destroy')->where(['id' => '[0-9]+']);
        });
        Route::controller(ProductCtrl::class)->group(function () {
            Route::get('/product', 'index');
            Route::post('/product/store', 'store');
            Route::get('/product/show/{id}', 'show')->where(['id' => '[0-9]+']);
            Route::put('/product/update/{id}', 'update')->where(['id' => '[0-9]+']);
            Route::delete('/product/destroy/{id}', 'destroy')->where(['id' => '[0-9]+']);
        });
        Route::controller(ContactUsCtrl::class)->group(function () {
            Route::get('/contact', 'index');
            Route::put('/contact/update', 'update');
        });
        Route::controller(AboutUsCtrl::class)->group(function () {
            Route::get('/about', 'index');
            Route::put('/about/update', 'update')->where(['id' => '[0-9]+']);
        });
    });
});
