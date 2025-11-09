<?php
use System\Router\Web\Route;

Route::get('', 'HomeController@index', 'index');
Route::get('create', 'HomeController@create', 'create');
Route::get('store', 'HomeController@store', 'store');
Route::post('edit/{id}', 'HomeController@edit', 'edit');
Route::put('update/{id}', 'HomeController@update', 'update');
Route::delete('delete/{id}', 'HomeController@destroy', 'delete');