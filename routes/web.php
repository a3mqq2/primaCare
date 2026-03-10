<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDispensingController;
use App\Http\Controllers\AdminMedicalRecordController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Language switch
Route::get('/{locale}/change', function (string $locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('locale.change');

// Auth routes
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->middleware('throttle:data-endpoint')->name('dashboard.data');
    Route::post('/impersonate/leave', [UserController::class, 'leaveImpersonation'])->name('impersonate.leave');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('role:system_admin,center_manager')->group(function () {
        Route::get('/centers', [CenterController::class, 'index'])->name('centers.index');
        Route::get('/centers/data', [CenterController::class, 'data'])->middleware('throttle:data-endpoint')->name('centers.data');
        Route::get('/centers/search', [CenterController::class, 'searchCenters'])->name('centers.search');
        Route::get('/centers/print', [CenterController::class, 'print'])->name('centers.print');
        Route::get('/centers/export', [ExportController::class, 'centers'])->middleware('throttle:export-endpoint')->name('centers.export');
        Route::get('/centers/create', [CenterController::class, 'create'])->name('centers.create');
        Route::post('/centers', [CenterController::class, 'store'])->name('centers.store');
        Route::get('/centers/{center}', [CenterController::class, 'show'])->name('centers.show');
        Route::put('/centers/{center}', [CenterController::class, 'update'])->name('centers.update');
        Route::delete('/centers/{center}', [CenterController::class, 'destroy'])->name('centers.destroy');

        Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
        Route::get('/cities/search', [CityController::class, 'search'])->name('cities.search');

        Route::get('/users/search-employees', [UserController::class, 'searchEmployees'])->name('users.search-employees');
    });

    Route::middleware('role:system_admin,center_manager')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'data'])->middleware('throttle:data-endpoint')->name('users.data');
        Route::get('/users/print', [UserController::class, 'print'])->name('users.print');
        Route::get('/users/export', [ExportController::class, 'users'])->middleware('throttle:export-endpoint')->name('users.export');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('role:system_admin,center_manager,center_employee')->group(function () {
        Route::get('/medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
        Route::get('/medical-records/data', [MedicalRecordController::class, 'data'])->middleware('throttle:data-endpoint')->name('medical-records.data');
        Route::post('/medical-records', [MedicalRecordController::class, 'store'])->name('medical-records.store');
        Route::get('/medical-records/search', [MedicalRecordController::class, 'search'])->name('medical-records.search');
        Route::get('/medical-records/check-national-id', [MedicalRecordController::class, 'checkNationalId'])->name('medical-records.check-national-id');
        Route::get('/medical-records/{medicalRecord}/print', [MedicalRecordController::class, 'printRecord'])->name('medical-records.print-record');
        Route::get('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'show'])->name('medical-records.show');
        Route::put('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'update'])->name('medical-records.update');
        Route::get('/medical-records/{medicalRecord}/dispensings', [MedicalRecordController::class, 'dispensings'])->name('medical-records.dispensings');
        Route::post('/medical-records/{medicalRecord}/dispense', [MedicalRecordController::class, 'dispense'])->name('medical-records.dispense');
        Route::get('/medicines/search', [MedicineController::class, 'search'])->name('medicines.search');
    });

    Route::middleware('role:system_admin')->group(function () {
        Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
        Route::get('/cities/data', [CityController::class, 'data'])->middleware('throttle:data-endpoint')->name('cities.data');
        Route::get('/cities/print', [CityController::class, 'print'])->name('cities.print');
        Route::get('/cities/export', [ExportController::class, 'cities'])->middleware('throttle:export-endpoint')->name('cities.export');
        Route::get('/cities/{city}', [CityController::class, 'show'])->name('cities.show');
        Route::put('/cities/{city}', [CityController::class, 'update'])->name('cities.update');
        Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');

        Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/admin/activity-logs/data', [ActivityLogController::class, 'data'])->middleware('throttle:data-endpoint')->name('admin.activity-logs.data');
        Route::get('/admin/activity-logs/print', [ActivityLogController::class, 'print'])->name('admin.activity-logs.print');

        Route::post('/impersonate/{user}', [UserController::class, 'impersonate'])->name('users.impersonate');

        Route::get('/admin/medical-records', [AdminMedicalRecordController::class, 'index'])->name('admin.medical-records.index');
        Route::get('/admin/medical-records/data', [AdminMedicalRecordController::class, 'data'])->middleware('throttle:data-endpoint')->name('admin.medical-records.data');
        Route::get('/admin/medical-records/print', [AdminMedicalRecordController::class, 'print'])->name('admin.medical-records.print');
        Route::get('/admin/medical-records/export', [ExportController::class, 'medicalRecords'])->middleware('throttle:export-endpoint')->name('admin.medical-records.export');
        Route::get('/admin/medical-records/{medicalRecord}/print', [AdminMedicalRecordController::class, 'printRecord'])->name('admin.medical-records.print-record');
        Route::get('/admin/medical-records/{medicalRecord}', [AdminMedicalRecordController::class, 'show'])->name('admin.medical-records.show');

        Route::get('/admin/dispensings', [AdminDispensingController::class, 'index'])->name('admin.dispensings.index');
        Route::get('/admin/dispensings/data', [AdminDispensingController::class, 'data'])->middleware('throttle:data-endpoint')->name('admin.dispensings.data');
        Route::get('/admin/dispensings/print', [AdminDispensingController::class, 'print'])->name('admin.dispensings.print');
        Route::get('/admin/dispensings/export', [ExportController::class, 'dispensings'])->middleware('throttle:export-endpoint')->name('admin.dispensings.export');
        Route::get('/admin/dispensings/{dispensing}', [AdminDispensingController::class, 'show'])->name('admin.dispensings.show');

        Route::get('/admin/statistics', [StatisticsController::class, 'index'])->name('admin.statistics.index');
        Route::get('/admin/statistics/data', [StatisticsController::class, 'data'])->middleware('throttle:data-endpoint')->name('admin.statistics.data');
        Route::get('/admin/statistics/print', [StatisticsController::class, 'print'])->name('admin.statistics.print');

        Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/medicines/data', [MedicineController::class, 'data'])->middleware('throttle:data-endpoint')->name('medicines.data');
        Route::get('/medicines/print', [MedicineController::class, 'print'])->name('medicines.print');
        Route::get('/medicines/export', [ExportController::class, 'medicines'])->middleware('throttle:export-endpoint')->name('medicines.export');
        Route::get('/medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('/medicines/dispensings', [MedicineController::class, 'dispensingHistory'])->name('medicines.dispensings');
        Route::get('/medicines/dispensings/data', [MedicineController::class, 'dispensingHistoryData'])->name('medicines.dispensings.data');
        Route::get('/medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');
        Route::put('/medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy'])->name('medicines.destroy');
    });
});
