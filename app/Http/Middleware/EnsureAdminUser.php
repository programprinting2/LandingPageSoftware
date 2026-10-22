<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, AdminAuthService::ADMIN_ROLES, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized.',
                ], 401);
            }

            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
