<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\InvoiceController;
use App\Http\Middleware\ManagerOnly;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('my.login.submit');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /* ---------------- Staff ---------------- */
    Route::view('/dashboard', 'vetcare.staff.dashboard')->name('vetcare.staff.dashboard');
    Route::view('/owners', 'vetcare.staff.owners')->name('vetcare.staff.owners');
    Route::view('/appointments', 'vetcare.staff.appointments')->name('vetcare.staff.appointments');
    Route::view('/treatments', 'vetcare.staff.treatments')->name('vetcare.staff.treatments');
    Route::view('/billing', 'vetcare.staff.billing')->name('vetcare.staff.billing');
    Route::view('/inventory', 'vetcare.staff.inventory')->name('vetcare.staff.inventory');

    /* ---------------- Manager (เฉพาะ role = manager) ---------------- */
    Route::middleware(ManagerOnly::class)->group(function () {
        Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('vetcare.manager.dashboard');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('vetcare.manager.users');
        Route::post('/users', [UserController::class, 'store'])->name('vetcare.manager.users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('vetcare.manager.users.update');
        Route::put('/users/{id}/password', [UserController::class, 'resetPassword'])->name('vetcare.manager.users.reset');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('vetcare.manager.users.destroy');

        // Medicines
        Route::get('/medicines', [MedicineController::class, 'index'])->name('vetcare.manager.medicines');
        Route::post('/medicines', [MedicineController::class, 'store'])->name('vetcare.manager.medicines.store');
        Route::put('/medicines/{id}', [MedicineController::class, 'update'])->name('vetcare.manager.medicines.update');
        Route::delete('/medicines/{id}', [MedicineController::class, 'destroy'])->name('vetcare.manager.medicines.destroy');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('vetcare.manager.invoices');
        Route::put('/invoices/{id}/void', [InvoiceController::class, 'void'])->name('vetcare.manager.invoices.void');
    });
});

require __DIR__.'/settings.php';