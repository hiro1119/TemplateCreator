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

Route::get('/', 'HomeController@index');
Route::post('/project/create', 'HomeController@createProject')->name('project.create');
Route::get('/project/module/{id}', 'HomeController@module')->name('project.module');
Route::post('/project/moduleup/{id}', 'HomeController@moduleUp')->name('project.moduleup');
Route::get('/project/info/{id}', 'HomeController@projectInfo')->name('project.info');
Route::get('/project/output/{id}', 'HomeController@outPutFiles')->name('project.output');
