<?php

declare(strict_types=1);

namespace App\Http\Controllers\Healthcheck;

use App\Http\Controllers\Controller;

class HealthcheckController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
