<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', 'ScrapController@test');
Route::group(['prefix' => 'hakore'], function () {
    Route::get('/all', 'HakoreController@getList')->name('get.novels');
    Route::get('/novel/{url}', 'HakoreController@getNovelDetail');
    Route::get('/chapter/{novel}/{chapter}', 'HakoreController@getChapterDetail');
    Route::get('/genrefilter', 'HakoreController@getGenreFilter');
    Route::get('/genrelisturl', 'HakoreController@getListByGenreUrl');
    Route::get('/search', 'HakoreController@search');
});
