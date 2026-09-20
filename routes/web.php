<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/admin-dashboard', 'pages.auth.admin.index')->name('admin.dashboard');
    Route::view('/staff-dashboard', 'pages.auth.staff.index')->name('staff.dashboard');
});


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('my.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

require __DIR__.'/settings.php';
