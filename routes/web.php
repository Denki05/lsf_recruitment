<?php

/*
|--------------------------------------------------------------------------
| Web Routes — Recruitment
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

// Frontend pelamar (publik, tanpa login)
Route::get('/', 'JobsController@index')->name('jobs.index');
Route::get('/c/{branch}', 'JobsController@branch')->name('jobs.branch');
Route::get('/loker/{id}', 'JobsController@show')->name('jobs.show')->where('id', '[0-9]+');
Route::get('/lamaran/sukses', 'ApplicationController@success')->name('lamaran.sukses');
Route::get('/lamaran/{position?}', 'ApplicationController@create')->name('lamaran.form')->where('position', '[0-9]+');
Route::post('/lamaran', 'ApplicationController@store')->middleware('throttle:10,1')->name('lamaran.store');

// Admin auth
Route::get('/admin/login', 'Admin\LoginController@showLogin')->name('admin.login');
Route::post('/admin/login', 'Admin\LoginController@login')->name('admin.login.submit');
Route::post('/admin/logout', 'Admin\LoginController@logout')->name('admin.logout');

// Admin area (wajib login)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', 'Admin\DashboardController@index')->name('dashboard');

    Route::get('/lamaran', 'Admin\ApplicationController@index')->name('applications.index');
    Route::get('/lamaran/banding', 'Admin\ApplicationController@compare')->name('applications.compare');
    Route::post('/lamaran/bulk-status', 'Admin\ApplicationController@bulkStatus')->name('applications.bulk');
    Route::post('/lamaran/bulk-filtered', 'Admin\ApplicationController@bulkFiltered')->name('applications.bulkFiltered');
    Route::get('/lamaran/export', 'Admin\ApplicationController@export')->name('applications.export');
    Route::get('/lamaran/{id}', 'Admin\ApplicationController@show')->name('applications.show');
    Route::post('/lamaran/{id}/status', 'Admin\ApplicationController@updateStatus')->name('applications.status');
    Route::post('/lamaran/{id}/ai', 'Admin\ApplicationController@evaluateAi')->name('applications.ai');
    Route::post('/lamaran/{id}/ai-reuse', 'Admin\ApplicationController@reuseAi')->name('applications.aiReuse');
    Route::get('/lamaran/{id}/download', 'Admin\ApplicationController@download')->name('applications.download');
    Route::get('/lamaran/{id}/preview', 'Admin\\ApplicationController@preview')->name('applications.preview');
    Route::delete('/lamaran/{id}', 'Admin\ApplicationController@destroy')->name('applications.destroy');

    Route::post('/cabang/switch', 'Admin\BranchController@switch')->name('branch.switch');
    Route::resource('cabang', 'Admin\BranchController')->names('branches')->except(['show']);
    Route::resource('pengguna', 'Admin\UserController')->names('users')->except(['show']);

    Route::resource('loker', 'Admin\PositionController')->names('positions')->except(['show']);
});
