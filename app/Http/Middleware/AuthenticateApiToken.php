<?php

namespace App\Http\Middleware;

use App\Services\ClientAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function __construct(private ClientAuthService $authService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak valid',
                'logged_in' => false,
            ], 401);
        }

        $user = $this->authService->resolveApiToken($token);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak valid',
                'logged_in' => false,
            ], 401);
        }

        if (! $this->authService->isClientActive($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun dinonaktifkan. Hubungi admin.',
                'logged_in' => false,
                'account_inactive' => true,
            ], 403);
        }

        $request->attributes->set('apiUser', $user);
        $request->attributes->set('apiToken', $token);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $auth = $request->header('Authorization', '');

        if (str_starts_with(strtolower($auth), 'bearer ')) {
            return trim(substr($auth, 7));
        }

        $token = $request->input('token');

        return is_string($token) && $token !== '' ? trim($token) : null;
    }
}
