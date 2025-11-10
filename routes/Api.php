<?php
namespace System\Router\Api;


Api::get('', 'HomeController@index', 'index');
Api::get('create', 'HomeController@create', 'create');
Api::get('store', 'HomeController@store', 'store');
Api::post('edit/{id}', 'HomeController@edit', 'edit');