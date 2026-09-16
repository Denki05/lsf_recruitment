<?php

/*
|--------------------------------------------------------------------------
| Web Routes — Recruitment
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

// Frontend pelamar (publik, tanpa login)
Route::get('/', 'ApplicationController@create')->name('lamaran.form');
Route::post('/lamaran', 'ApplicationController@store')->name('lamaran.store');
Route::get('/lamaran/sukses/{id}', 'ApplicationController@success')->name('lamaran.sukses');

// Admin auth
Route::get('/admin/login', 'Admin\LoginController@showLogin')->name('admin.login');
Route::post('/admin/login', 'Admin\LoginController@login')->name('admin.login.submit');
Route::post('/admin/logout', 'Admin\LoginController@logout')->name('admin.logout');

// Admin area (wajib login)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', 'Admin\DashboardController@index')->name('dashboard');

    Route::get('/lamaran', 'Admin\ApplicationController@index')->name('applications.index');
    Route::get('/lamaran/export', 'Admin\ApplicationController@export')->name('applications.export');
    Route::get('/lamaran/{id}', 'Admin\ApplicationController@show')->name('applications.show');
    Route::post('/lamaran/{id}/status', 'Admin\ApplicationController@updateStatus')->name('applications.status');
    Route::get('/lamaran/{id}/download', 'Admin\ApplicationController@download')->name('applications.download');
    Route::delete('/lamaran/{id}', 'Admin\ApplicationController@destroy')->name('applications.destroy');

    Route::resource('loker', 'Admin\PositionController')->names('positions')->except(['show']);
});
