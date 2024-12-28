<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class CheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token bulunamadı'
            ], 401);
        }

        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Yetkisiz erişim'
            ], 401);
        }

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Token geçerli'
        ]);
    }
}
