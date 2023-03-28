<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;
use App\Http\Controllers\ProductController;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get( 'products', 'ProductController@index' );
Route::get( '/test/{id?}', function($id=25){
    return 'test '. $id;
} );
Route::get( 'json/response', function(){
    return response()->json([
        'name'=>"trx",
        'speed'=>'200kph'
    ],205);
} );
Route::get( 'download', function(){
    return response()->download('C:\Users\User\Desktop\Dream\routes\file.txt');
} );

Route::get( '/blog1', 'BlogController@rnfn' );