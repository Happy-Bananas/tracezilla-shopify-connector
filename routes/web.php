<?php

use App\Http\Controllers\ShopifyTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/shopify', [ShopifyTestController::class, 'show'])
    ->name('shopify.test');

Route::post('/shopify/test', [ShopifyTestController::class, 'test'])
    ->name('shopify.test.run');

Route::post('/shopify/list-products', [ShopifyTestController::class, 'listProducts'])
    ->name('shopify.products.run');