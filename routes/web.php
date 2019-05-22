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
Route::post('/project/get-modules/{id}', 'HomeController@getModules')->name('project.getModules');
Route::get('/docs/new-pj', 'DocsController@newProject')->name('docs.new');
Route::get('/docs/mod-url', 'DocsController@moduleUpUrl')->name('docs.modup');
Route::get('/docs/mod-input', 'DocsController@moduleUpInput')->name('docs.modinput');
Route::get('/docs/si-create', 'DocsController@siteInfoCreate')->name('docs.siteinfo');
Route::get('/docs/file-out', 'DocsController@fileOutPut')->name('docs.siteinfo');
