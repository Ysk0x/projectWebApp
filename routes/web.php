<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
<<<<<<< Updated upstream
    Route::view('/admin-dashboard', 'pages.auth.admin.index')->name('admin.dashboard');
    Route::view('/staff-dashboard', 'pages.auth.staff.index')->name('staff.dashboard');
});

=======
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('/admin-dashboard', 'vetcare.manager.dashboard')->name('manager.dashboard');
    Route::view('/staff-dashboard', 'vetcare.staff.dashboard')->name('staff.dashboard');
});


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('my.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

>>>>>>> Stashed changes

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('my.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

<<<<<<< Updated upstream
require __DIR__.'/settings.php';
=======
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
>>>>>>> Stashed changes
