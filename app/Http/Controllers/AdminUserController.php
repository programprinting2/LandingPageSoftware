<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminUserAffiliateRequest;
use App\Http\Requests\UpdateAdminUserRoleRequest;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with([
                'client.applications:id,name',
                'affiliate:id,user_id,code,status',
            ])
            ->latest('id')
            ->get()
            ->map(fn (User $user) => $this->formatUser($user));

        return response()->json([
            'users' => $users,
        ]);
    }

    public function updateRole(UpdateAdminUserRoleRequest $request, User $user): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $newRole = $request->validated('role');

        if ($newRole === 'affiliate') {
            throw ValidationException::withMessages([
                'role' => 'Role affiliate tidak digunakan lagi. Aktifkan affiliate dari profil client.',
            ]);
        }

        if ($actor->id === $user->id && $user->role === 'super_admin' && $newRole !== 'super_admin') {
            $otherSuperAdmins = User::query()
                ->where('role', 'super_admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherSuperAdmins === 0) {
                throw ValidationException::withMessages([
                    'role' => 'Tidak dapat menurunkan role: Anda satu-satunya super admin.',
                ]);
            }
        }

        if ($newRole === 'client' && ! $user->client) {
            throw ValidationException::withMessages([
                'role' => 'User belum punya profil client. Buat/tambah client terlebih dahulu.',
            ]);
        }

        $user->update(['role' => $newRole]);

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'user' => $this->formatUser($user->fresh(['client.applications:id,name', 'affiliate:id,user_id,code,status'])),
        ]);
    }

    public function updateAffiliate(UpdateAdminUserAffiliateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $enabled = (bool) $validated['enabled'];

        if ($enabled && ! $user->client && ! $user->affiliate) {
            throw ValidationException::withMessages([
                'enabled' => 'Affiliate hanya untuk user yang punya profil client. Tambah client terlebih dahulu.',
            ]);
        }

        DB::transaction(function () use ($user, $validated, $enabled) {
            if ($enabled) {
                $status = $validated['status'] ?? 'active';
                $affiliate = $this->affiliateService->provisionForUser(
                    $user,
                    $validated['code'] ?? null,
                    $status,
                );

                if (! empty($validated['code'])) {
                    $affiliate->update(['code' => strtoupper(trim($validated['code']))]);
                }

                if ($user->role === 'client') {
                    return;
                }

                if ($user->client) {
                    $user->update(['role' => 'client']);
                }
            } elseif ($user->affiliate) {
                $user->affiliate->update(['status' => 'suspended']);
            }
        });

        return response()->json([
            'message' => $enabled ? 'Affiliate diaktifkan.' : 'Affiliate dinonaktifkan.',
            'user' => $this->formatUser($user->fresh(['client.applications:id,name', 'affiliate:id,user_id,code,status'])),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        /** @var User|null $actor */
        $actor = request()->user();

        if ($actor && $actor->id === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'Tidak dapat menghapus akun yang sedang login.',
            ]);
        }

        if ($user->role === 'super_admin') {
            $others = User::query()
                ->where('role', 'super_admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($others === 0) {
                throw ValidationException::withMessages([
                    'user' => 'Tidak dapat menghapus satu-satunya super admin.',
                ]);
            }
        }

        DB::transaction(function () use ($user) {
            $user->affiliate?->delete();
            $user->client?->delete();
            $user->delete();
        });

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    private function formatUser(User $user): array
    {
        $client = $user->client;
        $affiliate = $user->affiliate;

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $this->roleLabel($user->role),
            'email_verified' => $user->email_verified_at !== null,
            'created_at' => $user->created_at?->format('d M Y'),
            'client_id' => $client?->id,
            'company_name' => $client?->company_name,
            'whatsapp' => $client?->whatsapp,
            'status' => $client?->status,
            'applications' => $client
                ? $client->applications->pluck('name')->values()->all()
                : [],
            'affiliate_id' => $affiliate?->id,
            'affiliate_code' => $affiliate?->code,
            'affiliate_status' => $affiliate?->status,
            'affiliate_enabled' => $affiliate !== null && (string) $affiliate->status === 'active',
            'affiliate_referral_url' => $affiliate
                ? $this->affiliateService->referralUrlForCode($affiliate->code)
                : null,
        ];
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'affiliate' => 'Affiliate',
            'client' => 'Client',
            default => ucfirst($role),
        };
    }
}
