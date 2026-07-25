<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiReferralController extends Controller
{
    public function show(string $code): JsonResponse
    {
        $normalized = Str::upper(trim($code));

        if (! preg_match('/^A[A-Z][0-9]{4}$/', $normalized)) {
            return response()->json([
                'status' => 'invalid',
                'valid' => false,
            ]);
        }

        $affiliate = Affiliate::query()
            ->with('user:id,name')
            ->where('code', $normalized)
            ->where('status', 'active')
            ->first();

        if (! $affiliate) {
            return response()->json([
                'status' => 'invalid',
                'valid' => false,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'code' => $affiliate->code,
            'name' => $affiliate->user?->name,
        ]);
    }
}
