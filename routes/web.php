<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AwsAccountController;
use App\Http\Controllers\AwsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    // Add forgot/reset/verify routes as needed
});

// User routes
Route::prefix('user')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('User/Dashboard'))->name('user.dashboard');
    Route::resource('aws-accounts', AwsAccountController::class);
    Route::resource('aws', AwsController::class);
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('admin.dashboard');
    Route::resource('aws-accounts', AwsAccountController::class);
    Route::resource('aws', AwsController::class);
});
