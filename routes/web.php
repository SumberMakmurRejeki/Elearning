<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->name('preview.login');
Route::view('/ui-preview/admin', 'admin.dashboard')->name('preview.admin');
Route::view('/ui-preview/employee', 'employee.dashboard')->name('preview.employee');
