<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::post('api/xpp', 'XppController@run');

    Route::get('documents', ['as' => 'document.list', 'uses' => 'DocumentsController@all']);
    Route::get('documents/public', ['as' => 'document.public', 'uses' => 'DocumentsController@publicDocs']);
    Route::get('documents/{id}', ['as' => 'document.show', 'uses' => 'DocumentsController@show']);
    Route::post('documents', 'DocumentsController@create');
    Route::post('documents/duplicate/{id}', 'DocumentsController@duplicate');
    Route::delete('documents/{id}', 'DocumentsController@delete');
    Route::put('documents/{id}', 'DocumentsController@update');

    /*
     * Project/document creation related pages
     */
    Route::group(['prefix' => 'create'], function () {
        Route::get('from-template', function () {
            return view('document.create.from-template');
        });

        Route::get('blank', function () {
            return view('document.create.blank');
        });
    });

    /*
     * Help related pages
     */
    Route::group(['prefix' => 'help'], function () {
        Route::get('changelog', function () {
            return view('changelog');
        });
    });
});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/home', 'HomeController@index');
});
