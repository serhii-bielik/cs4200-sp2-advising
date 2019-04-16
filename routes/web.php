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
Route::get('/adviser/interval', 'AdviserController@getInterval');
Route::post('/adviser/interval', 'AdviserController@setInterval');
Route::get('/adviser/notification', 'AdviserController@getNotification');
Route::post('/adviser/notification', 'AdviserController@setNotification');
Route::get('/adviser/messages/{studentID}', 'AdviserController@messages');
Route::post('/adviser/messages', 'AdviserController@addMessage');

Route::get('/student', 'StudentController@dashboard');
Route::get('/student/note', 'StudentController@note');
Route::get('/student/adviser', 'StudentController@adviser');
Route::get('/student/info', 'StudentController@info');
Route::get('/student/notification', 'StudentController@getNotification');
Route::post('/student/notification', 'StudentController@setNotification');
Route::get('/student/messages', 'StudentController@messages');
Route::post('/student/messages', 'StudentController@addMessage');

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
