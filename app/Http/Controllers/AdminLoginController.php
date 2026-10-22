<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminLoginRequest;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function __construct(private AdminAuthService $authService) {}

    public function store(StoreAdminLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));

        $user = $this->authService->attemptAdminLogin($email, $validated['password']);

        if (! $user) {
            return response()->json([
                'message' => 'Email atau password salah, atau akun bukan admin.',
            ], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'admin' => $this->authService->adminPayload($user),
        ]);
    }
}
