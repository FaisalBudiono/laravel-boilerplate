<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Healthcheck\HealthcheckController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HealthcheckController::class, 'index'])->name('healthcheck');

Route::post('/auths/login', [AuthController::class, 'store'])->name('auth.login');
Route::get('/me', [UserController::class, 'me'])->name('me');
