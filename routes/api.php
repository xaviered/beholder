<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Database\Models\App;
use App\Database\Models\Resource;

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

Route::middleware( 'auth:api' )->get( '/user', function( Request $request ) {
	return $request->user();
} )
;

// App controller
//Route::middleware('auth:api')->resource( App::ROUTE_NAME, 'AppController' );
Route::resource( App::ROUTE_NAME, 'AppController' );

// Resource controller
//Route::resource( Resource::ROUTE_NAME, 'ResourceController' );

// index
Route::get( 'app/{app}/{type}', 'ResourceController@index' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->name( 'resource.index' )
;

// show
Route::get( 'app/{app}/{type}/{resource}', 'ResourceController@show' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->where( 'resource', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->name( 'resource.show' )
;

// store
Route::post( 'app/{app}/{type}', 'ResourceController@store' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->name( 'resource.store' )
;

//Route::post($uri, $callback);
//Route::put($uri, $callback);
//Route::patch($uri, $callback); // update
//Route::delete($uri, $callback); // delete
