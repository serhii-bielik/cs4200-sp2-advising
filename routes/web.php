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
    return view('welcome');
});

Route::get('/director/faculties', 'DirectorController@faculties');
Route::get('/director/advisers', 'DirectorController@advisers');
Route::get('/director/students', 'DirectorController@students');
Route::post('/director/assign', 'DirectorController@assign');
Route::post('/director/dismiss', 'DirectorController@dismiss');

Route::get('/adviser', 'AdviserController@dashboard');
Route::get('/adviser/students', 'AdviserController@students');

Route::get('/student', 'StudentController@dashboard');

Route::get('/admin/advisers', 'AdminController@advisers');
Route::post('/admin/advisers', 'AdminController@advisersUpload');
//Route::get('/admin/advisers/update', 'AdminController@advisersUpdate');
//Route::get('/admin/advisers/remove', 'AdminController@advisersRemove');
Route::get('/admin/students', 'AdminController@students');
Route::post('/admin/students', 'AdminController@studentsUpload');

//Auth::routes();

Route::get('/login', 'Auth\LoginController@redirectToProvider');
Route::post('login', [ 'as' => 'login', 'uses' => 'Auth\LoginController@redirectToProvider']);
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/callback', 'Auth\LoginController@handleProviderCallback');
