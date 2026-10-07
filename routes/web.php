<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\StaffInventoryController;
use App\Http\Middleware\ManagerOnly;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('my.login.submit');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /* ---------------- Staff ---------------- */
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('vetcare.staff.dashboard');

    // Owners + Pets
    Route::get('/owners', [OwnerController::class, 'index'])->name('vetcare.staff.owners');
    Route::post('/owners', [OwnerController::class, 'storeOwner'])->name('vetcare.staff.owners.store');
    Route::put('/owners/{id}', [OwnerController::class, 'updateOwner'])->name('vetcare.staff.owners.update');
    Route::delete('/owners/{id}', [OwnerController::class, 'destroyOwner'])->name('vetcare.staff.owners.destroy');
    Route::post('/pets', [OwnerController::class, 'storePet'])->name('vetcare.staff.pets.store');
    Route::put('/pets/{id}', [OwnerController::class, 'updatePet'])->name('vetcare.staff.pets.update');
    Route::delete('/pets/{id}', [OwnerController::class, 'destroyPet'])->name('vetcare.staff.pets.destroy');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('vetcare.staff.appointments');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('vetcare.staff.appointments.store');
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->name('vetcare.staff.appointments.update');
    Route::put('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('vetcare.staff.appointments.cancel');

    // Treatments
    Route::get('/treatments', [TreatmentController::class, 'index'])->name('vetcare.staff.treatments');
    Route::post('/treatments', [TreatmentController::class, 'store'])->name('vetcare.staff.treatments.store');
    Route::put('/treatments/{id}', [TreatmentController::class, 'update'])->name('vetcare.staff.treatments.update');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('vetcare.staff.billing');
    Route::post('/billing/{id}/pay', [BillingController::class, 'pay'])->name('vetcare.staff.billing.pay');

    // Inventory (ดูอย่างเดียว)
    Route::get('/inventory', [StaffInventoryController::class, 'index'])->name('vetcare.staff.inventory');

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