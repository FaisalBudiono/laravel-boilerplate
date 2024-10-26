<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auths/login', [AuthController::class, 'store'])->name('auth.login');
Route::post('/auths/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');

Route::get('/me', [UserController::class, 'me'])->name('me');
