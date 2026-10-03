<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

Route::view('/login', 'vetcare.login')->name('vetcare.login');

Route::view('/dashboard', 'vetcare.staff.dashboard')
    ->name('vetcare.staff.dashboard');

Route::view('/owners', 'vetcare.staff.owners')
    ->name('vetcare.staff.owners');

Route::view('/appointments', 'vetcare.staff.appointments')
    ->name('vetcare.staff.appointments');

Route::view('/treatments', 'vetcare.staff.treatments')
    ->name('vetcare.staff.treatments');

Route::view('/billing', 'vetcare.staff.billing')
    ->name('vetcare.staff.billing');

Route::view('/inventory', 'vetcare.staff.inventory')
    ->name('vetcare.staff.inventory');

Route::view('/manager/dashboard', 'vetcare.manager.dashboard')
    ->name('vetcare.manager.dashboard');

Route::view('/users', 'vetcare.manager.users')
    ->name('vetcare.manager.users');

Route::view('/medicines', 'vetcare.manager.medicines')
    ->name('vetcare.manager.medicines');

Route::view('/invoices', 'vetcare.manager.invoices')
    ->name('vetcare.manager.invoices');

require __DIR__.'/settings.php';