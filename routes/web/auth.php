<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('login', [LoginAuthController::class, 'index'])->name('login');
Route::post('login', [LoginAuthController::class, 'login'])
    ->middleware('throttle:login');
Route::post('logout', [LoginAuthController::class, 'logout'])->name('logout');
Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::get('registration-verfication', [RegisterController::class, 'verfication'])->name('registration-verfication');
Route::get('password/reset', [ForgotPasswordController::class, 'index'])->name('password.request');
Route::post('password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
Route::get('password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::post('/change-password', [PasswordController::class, 'changePassword'])
    ->name('password.change')
    ->middleware('auth');
