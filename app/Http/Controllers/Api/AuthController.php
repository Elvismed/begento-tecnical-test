<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function store(LoginRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->issueToken($request->validated()),
        );
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revoked.',
        ]);
    }
}
