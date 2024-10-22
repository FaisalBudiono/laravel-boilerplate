<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Healthcheck\HealthcheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HealthcheckController::class, 'index']);

Route::post('/auths/login', [AuthController::class, 'store']);
