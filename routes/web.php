<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::redirect('Admin', '/admin');
});

Route::middleware(['auth', 'verified', 'can:manage-users'])->group(function () {
    Route::get('admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('admin/users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::put('admin/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('Login', [LoginController::class, 'index'])->name('demo.login');
    Route::post('Login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('demo.login.authenticate');
});

Route::post('Logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('demo.logout');

require __DIR__.'/settings.php';