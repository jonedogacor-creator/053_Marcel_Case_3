<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SellerProfilePhotoController;
use App\Http\Controllers\StudentProfilePhotoController;
use App\Livewire\ProductManager;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', ProductManager::class)->name('dashboard');
    Route::get('/inventaris', fn () => redirect()->route('dashboard'))->name('inventory');
    Route::get('/profiles/{studentProfileId}/photo', StudentProfilePhotoController::class)
        ->whereNumber('studentProfileId')
        ->name('profile.photo');
    Route::get('/seller/photo', SellerProfilePhotoController::class)->name('seller.photo');
});
