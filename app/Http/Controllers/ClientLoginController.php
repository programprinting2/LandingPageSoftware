<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientLoginRequest;
use App\Services\ClientAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientLoginController extends Controller
{
    public function __construct(private ClientAuthService $authService) {}

    public function store(StoreClientLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->authService->attemptClientLogin(
            $validated['email'],
            $validated['password']
        );

        if (! $user) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'client' => $this->authService->clientPayload($user),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
