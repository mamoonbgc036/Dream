<?php

use App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return redirect()->to('/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
    Route::resource('product-variant', 'VariantController');
    Route::resource('product', 'ProductController');
    Route::get('/all', 'ProductController@test');
    Route::get('/variants', 'ProductController@variants');
    Route::post('/product_image', 'ProductController@image');
    Route::post('/product_image/{product_id}', 'ProductController@updateImages');
    Route::resource('/blog', 'BlogController');
    Route::resource('blog-category', 'BlogCategoryController');
});