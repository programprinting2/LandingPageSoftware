<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiLoginRequest;
use App\Http\Requests\StoreApiRegistrationRequest;
use App\Services\ClientAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAuthController extends Controller
{
    public function __construct(private ClientAuthService $authService) {}

    public function register(StoreApiRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $name = trim($validated['name'] ?? '') ?: strstr($email, '@', true) ?: $email;

        $result = $this->authService->registerClient([
            'name' => $name,
            'email' => $email,
            'password' => $validated['password'],
            'company_name' => trim($validated['company_name'] ?? '') ?: $name,
            'whatsapp' => trim($validated['whatsapp'] ?? '') ?: '',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Periksa email Anda untuk link konfirmasi sebelum login.',
            'requires_verification' => true,
            'email' => $result['user']->email,
        ], 201);
    }

    public function login(StoreApiLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $user = $this->authService->attemptClientLogin($email, $validated['password']);

        if (! $user) {
            $unverified = $this->authService->findUnverifiedClientByCredentials($email, $validated['password']);

            if ($unverified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email belum diverifikasi. Periksa inbox atau kirim ulang link konfirmasi.',
                    'requires_verification' => true,
                    'email' => $unverified->email,
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        return response()->json(
            $this->authService->apiSessionPayload($user, 'Login berhasil')
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('apiToken');

        if (is_string($token)) {
            $this->authService->revokeApiToken($token);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');

        return response()->json([
            'status' => 'success',
            'logged_in' => true,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at?->toIso8601String(),
        ]);
    }
}
