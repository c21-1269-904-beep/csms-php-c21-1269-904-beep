<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'application' => config('app.name'),
            'version' => config('app.version'),
            'status' => 'ok',
        ]);
    }
}