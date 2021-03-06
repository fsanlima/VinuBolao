<?php

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
    return redirect('/bolao');
});

Auth::routes();

Route::group(['middleware' => ['auth', 'can:admin'], 'prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\\'], function() {
    Route::resource('user', 'UserController');
});

Route::group(['middleware' => 'auth'], function() {
    Route::get('classificacao', 'BolaoController@classificacao')->name('classificacao');
    Route::get('regulamento', 'BolaoController@regulamento')->name('regulamento');
    Route::resource('bolao', 'BolaoController');

    Route::resource('participante', 'ParticipanteController');

    Route::get('participante', 'ParticipanteController@index')->name('participante.index');

    Route::get('jogo', 'JogoController@index')->name('jogo.index');

    Route::get('palpite', 'PalpiteController@index')->name('palpite.index');
});