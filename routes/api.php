<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('me', [UserController::class, 'me'])->name('me')->middleware('jwt');
Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');

Route::get('users', [UserController::class, 'index'])->name('user.index');
Route::post('users', [UserController::class, 'store'])->name('user.store');
Route::delete('users/{userID}', [UserController::class, 'destroy'])->name('user.destroy');
Route::get('users/{userID}', [UserController::class, 'show'])->name('user.show');
Route::put('users/{userID}', [UserController::class, 'update'])->name('user.update');

Route::get('posts', [PostController::class, 'index'])->name('post.index');
Route::post('posts', [PostController::class, 'store'])->name('post.store');
Route::get('posts/{postID}', [PostController::class, 'show'])->name('post.show');
