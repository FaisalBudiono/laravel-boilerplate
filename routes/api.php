<?php

use App\Http\Controllers\Healthcheck\HealthcheckController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('', [HealthcheckController::class, 'index'])->name('healthcheck');

Route::get('users', [UserController::class, 'index'])->name('user.index');
Route::post('users', [UserController::class, 'store'])->name('user.store');
Route::delete('users/{userID}', [UserController::class, 'destroy'])->name('user.destroy');
Route::get('users/{userID}', [UserController::class, 'show'])->name('user.show');
Route::put('users/{userID}', [UserController::class, 'update'])->name('user.update');
