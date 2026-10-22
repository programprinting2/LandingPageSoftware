<?php

namespace App\Http\Controllers;

use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSessionController extends Controller
{
    public function __construct(private AdminAuthService $authService) {}

    public function me(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'logged_in' => false,
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! in_array($user->role, AdminAuthService::ADMIN_ROLES, true)) {
            return response()->json([
                'logged_in' => false,
            ]);
        }

        return response()->json([
            'logged_in' => true,
            'admin' => $this->authService->adminPayload($user),
        ]);
    }
}
