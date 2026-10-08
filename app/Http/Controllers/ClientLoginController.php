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
        $email = strtolower(trim($validated['email']));

        $user = $this->authService->attemptClientLogin($email, $validated['password']);

        if (! $user) {
            $unverified = $this->authService->findUnverifiedClientByCredentials($email, $validated['password']);

            if ($unverified) {
                return response()->json([
                    'message' => 'Email belum diverifikasi. Periksa inbox atau kirim ulang link konfirmasi.',
                    'requires_verification' => true,
                    'email' => $unverified->email,
                ], 403);
            }

            $inactive = $this->authService->findInactiveClientByCredentials($email, $validated['password']);

            if ($inactive) {
                return response()->json([
                    'message' => 'Akun dinonaktifkan. Hubungi admin.',
                    'account_inactive' => true,
                ], 403);
            }

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
