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

// User (Common) Routes

Route::get('/whoami', 'UserController@getUserInfo');
Route::get('/faculties', 'UserController@faculties');
Route::get('/period', 'UserController@period');

Route::get('/notifications', 'UserController@getNotifications');
Route::post('/notifications/read', 'UserController@readNotification');
Route::post('/notifications/readAll', 'UserController@readAllNotifications');

// Director Routes

Route::get('/director/advisers', 'DirectorController@advisers');
Route::get('/director/students', 'DirectorController@students');
Route::get('/director/student/{studentId}', 'DirectorController@student');
Route::post('/director/student', 'DirectorController@updateStudent');
Route::post('/director/student/remove', 'AdminController@studentRemove');
Route::post('/director/student/suspend', 'AdminController@studentSuspend');
Route::post('/director/student/activate', 'AdminController@studentActivate');
Route::get('/director/unassignedStudents', 'DirectorController@unassignedStudents');
Route::get('/director/assignedStudents', 'DirectorController@assignedStudents');
Route::post('/director/assign', 'DirectorController@assign');
Route::post('/director/dismiss', 'DirectorController@dismiss');
Route::get('/director/periods', 'DirectorController@periods');
Route::post('/director/periods', 'DirectorController@addPeriod');
Route::post('/director/period/remove', 'DirectorController@removePeriod');
Route::post('/director/period/notify', 'DirectorController@notifyPeriod');
Route::get('/director/period/notify/status', 'DirectorController@notifyPeriodStatus');
Route::get('/director/period/{periodId}/report', 'DirectorController@reportForPeriod');
Route::get('/director/period/{periodId}/report/reserved', 'DirectorController@reportForPeriodReserved');
Route::get('/director/period/{periodId}/report/unreserved', 'DirectorController@reportForPeriodUnreserved');
Route::get('/director/period/{periodId}/report/attended', 'DirectorController@reportForPeriodAttended');
Route::get('/director/period/{periodId}/report/cancelled', 'DirectorController@reportForPeriodCancelled');
Route::get('/director/period/{periodId}/report/missed', 'DirectorController@reportForPeriodMissed');
Route::get('/director/report', 'DirectorController@report');
Route::get('/director/adviser/{adviserId}', 'DirectorController@adviserData');

// Adviser Routes

//Route::get('/adviser', 'AdviserController@dashboard');
Route::get('/adviser/students', 'AdviserController@students');
Route::get('/adviser/student/{studentId}', 'AdviserController@student');
Route::get('/adviser/settings', 'UserController@settings');
Route::post('/adviser/settings', 'UserController@setSettings');
Route::get('/adviser/messages/{studentId}', 'AdviserController@messages');
Route::post('/adviser/messages', 'AdviserController@addMessage');
Route::get('/adviser/notes/public/{studentId}', 'AdviserController@getPublicNotes');
Route::post('/adviser/notes/public/remove', 'AdviserController@removePublicNote');
Route::post('/adviser/notes/public', 'AdviserController@addPublicNote');
Route::get('/adviser/notes/private/{studentId}', 'AdviserController@getPrivateNotes');
Route::post('/adviser/notes/private/remove', 'AdviserController@removePrivateNote');
Route::post('/adviser/notes/private', 'AdviserController@addPrivateNote');
Route::get('/adviser/timeslots', 'AdviserController@timeslots');
Route::post('/adviser/timeslots/notify', 'AdviserController@notifyTimeslots');
Route::get('/adviser/timeslots/{date}', 'AdviserController@timeslotsByDate');
Route::post('/adviser/timeslots/{date}', 'AdviserController@addTimeslotForDate');
Route::post('/adviser/timeslots/{date}/update', 'AdviserController@updateTimeslotsForDate');
Route::post('/adviser/timeslot/remove', 'AdviserController@removeTimeslot');
Route::get('/adviser/reservations', 'AdviserController@getReservations');
Route::get('/adviser/unconfirmedReservations', 'AdviserController@getUnconfirmedReservations');
Route::post('/adviser/reservation/cancel', 'AdviserController@cancelReservation');
Route::post('/adviser/reservation/attend', 'AdviserController@attendReservation');
Route::post('/adviser/reservation/miss', 'AdviserController@missReservation');
Route::post('/adviser/reservation/confirm', 'AdviserController@confirmReservation');
Route::get('/adviser/dashboard', 'AdviserController@getStats');

// Student Routes

// Route::get('/student', 'StudentController@dashboard');
Route::get('/student/note', 'StudentController@note');
Route::get('/student/notes', 'StudentController@notes');
Route::get('/student/adviser', 'StudentController@adviser');
Route::get('/student/info', 'StudentController@info');
Route::get('/student/notification', 'StudentController@getNotification');
Route::post('/student/notification', 'StudentController@setNotification');
Route::get('/student/messages/recent', 'StudentController@recentMessages');
Route::get('/student/messages', 'StudentController@messages');
Route::post('/student/messages', 'StudentController@addMessage');
Route::get('/student/settings', 'UserController@settings');
Route::post('/student/settings', 'UserController@setSettings');
Route::get('/student/timeslots', 'StudentController@timeslots');
Route::get('/student/timeslots/{date}', 'StudentController@timeslotsByDate');
Route::get('/student/reservation', 'StudentController@getReservation');
Route::post('/student/reservation/make', 'StudentController@makeReservation');
Route::post('/student/reservation/cancel', 'StudentController@cancelReservation');

// Admin Routes

Route::get('/admin', 'AdminController@advisers');
Route::post('/admin', 'AdminController@advisersUpload');
Route::get('/admin/system', 'AdminController@system');
Route::get('/admin/systemReset', 'AdminController@systemReset');
Route::post('/admin/removeUser', 'AdminController@removeUser');
Route::get('/admin/userRemoval', 'AdminController@massUserRemoval');
Route::post('/admin/userRemoval', 'AdminController@massUserRemovalUpload');
Route::get('/admin/userData/{userId}', 'AdminController@userData');
Route::post('/admin/userData', 'AdminController@updateUserData');
Route::get('/admin/students', 'AdminController@students');
Route::post('/admin/students', 'AdminController@studentsUpload');

//Auth::routes();

Route::get('/login', 'Auth\LoginController@redirectToProvider');
Route::post('/login', [ 'as' => 'login', 'uses' => 'Auth\LoginController@redirectToProvider']);
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/callback', 'Auth\LoginController@handleProviderCallback');
