<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public const ADMIN_ROLES = ['admin', 'super_admin'];

    public function attemptAdminLogin(string $email, string $password): ?User
    {
        $user = User::query()->where('email', strtolower(trim($email)))->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (! in_array($user->role, self::ADMIN_ROLES, true)) {
            return null;
        }

        return $user;
    }

    public function adminPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_super_admin' => $user->role === 'super_admin',
        ];
    }
}
