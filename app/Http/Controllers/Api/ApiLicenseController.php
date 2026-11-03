<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientDeviceSlotCheckoutRequest;
use App\Services\ClientCommerceService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiLicenseController extends Controller
{
    public function __construct(
        private LicenseService $licenseService,
        private ClientCommerceService $commerceService,
    ) {}

    public function status(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $programId = (string) $request->query('program_id', '');
        $applicationSlug = $this->applicationSlug($request);

        $result = $this->licenseService->status($user, $programId, $applicationSlug);

        if (($result['status'] ?? '') === 'error') {
            return response()->json($result, 400);
        }

        return response()->json(array_merge(['status' => 'success'], $result));
    }

    public function activate(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $data = $request->json()->all();
        $programId = (string) ($data['program_id'] ?? '');
        $access = is_array($data['access'] ?? null) ? $data['access'] : null;
        $applicationSlug = $this->applicationSlug($request, $data);

        $result = $this->licenseService->activate($user, $programId, $access, $applicationSlug);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    public function deactivate(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $data = $request->json()->all();
        $programId = (string) ($data['program_id'] ?? '');
        $applicationSlug = $this->applicationSlug($request, $data);

        $result = $this->licenseService->deactivate($user, $programId, $applicationSlug);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    public function devices(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $programId = (string) $request->query('program_id', '');
        $applicationSlug = $this->applicationSlug($request);

        $result = $this->licenseService->listDevicesPublic($user, $programId, $applicationSlug);

        if (($result['status'] ?? '') === 'error') {
            return response()->json($result, 400);
        }

        return response()->json(array_merge(['status' => 'success'], $result));
    }

    public function upgrade(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $data = $request->json()->all();
        $packageId = (int) ($data['package_id'] ?? 0);
        $applicationSlug = $this->applicationSlug($request, $data);
        $referralCode = isset($data['referral_code']) ? (string) $data['referral_code'] : null;
        $totalDevices = isset($data['total_devices']) ? (int) $data['total_devices'] : null;

        $result = $this->licenseService->upgradePackage($user, $packageId, $applicationSlug, $referralCode, $totalDevices);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    public function checkoutDeviceSlots(StoreClientDeviceSlotCheckoutRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');
        $validated = $request->validated();
        $referral = isset($validated['referral_code']) ? (string) $validated['referral_code'] : null;
        $slug = $this->applicationSlug($request, $validated);

        $result = $this->commerceService->beginDeviceSlotCheckout(
            $user,
            $slug,
            (int) $validated['additional_slots'],
            $referral,
        );

        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    private function applicationSlug(Request $request, ?array $body = null): string
    {
        $body ??= $request->json()->all();

        return (string) (
            $body['application_slug']
            ?? $request->query('application_slug')
            ?? 'spot-color'
        );
    }
}
