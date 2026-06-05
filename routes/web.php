<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::resource('children', ChildController::class)->only(['index', 'show'])->middleware('role:admin,petugas');
    Route::resource('children', ChildController::class)->except(['index', 'show'])->middleware('role:petugas');
    Route::get('children/{child}/export/pdf', [ChildController::class, 'exportPdf'])->name('children.export-pdf')->middleware('role:admin,petugas');
    Route::resource('measurements', MeasurementController::class)->except('show')->middleware('role:admin,petugas');
    Route::middleware('role:admin')->group(function () {
        Route::resource('posyandus', PosyanduController::class)->except('show');
        Route::resource('devices', DeviceController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
