<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdmin\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog/{slug}', [HomeController::class, 'show'])->name('post.show');

// ── Auth ────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [OtpController::class, 'showRequestForm'])->name('login');
    Route::post('/login/otp/send', [OtpController::class, 'sendOtp'])->name('otp.send');
    Route::get('/login/otp/verify', [OtpController::class, 'showVerifyForm'])->name('otp.verify.form');
    Route::post('/login/otp/verify', [OtpController::class, 'verifyOtp'])->name('otp.verify');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Admin (any authenticated user) ──────────────────────────────────────────
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('posts',      PostController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('tags',       TagController::class)->except(['show', 'create', 'edit']);
    Route::post('/upload-image',  [PostController::class, 'uploadImage'])->name('upload.image');
});

// ── Super Admin ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('tenants', TenantController::class);
});
