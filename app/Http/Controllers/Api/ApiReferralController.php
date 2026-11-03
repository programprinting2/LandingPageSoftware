<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\CheckoutReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiReferralController extends Controller
{
    public function __construct(
        private CheckoutReferralService $checkoutReferral,
    ) {}

    public function show(Request $request, string $code): JsonResponse
    {
        $application = null;
        $slug = trim((string) $request->query('application', ''));

        if ($slug !== '') {
            $application = Application::query()->where('slug', $slug)->first();
        }

        $resolved = $this->checkoutReferral->resolve($code, $application);

        if (! $resolved) {
            return response()->json([
                'status' => 'invalid',
                'valid' => false,
            ]);
        }

        if ($resolved['type'] === 'affiliate') {
            $affiliate = $resolved['affiliate'];
            $affiliate?->loadMissing('user:id,name');

            return response()->json([
                'status' => 'success',
                'valid' => true,
                'type' => 'affiliate',
                'code' => $resolved['code'],
                'name' => $affiliate?->user?->name,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'type' => 'promo',
            'code' => $resolved['code'],
            'discount_percent' => $resolved['discount_percent'],
            'label' => $resolved['promo_voucher']?->label,
        ]);
    }
}
