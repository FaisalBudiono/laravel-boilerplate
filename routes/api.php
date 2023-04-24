<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


Route::post('users', [UserController::class, 'store'])->name('user.store');
