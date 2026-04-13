<?php

namespace App\Http\Controllers;

use App\Support\RealtimeSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function __construct(private RealtimeSnapshot $realtimeSnapshot) {}

    public function snapshot(Request $request): JsonResponse
    {
        return response()->json([
            'meta' => [
                'generatedAt' => now()->toIso8601String(),
            ],
            'channels' => $this->realtimeSnapshot->forUser($request->user()),
        ]);
    }
}
