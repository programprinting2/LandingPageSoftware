<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function show(Request $request, string $orderId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');

        $result = $this->paymentService->getOrderStatus($user, $orderId);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    public function simulate(Request $request, string $orderId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');

        $result = $this->paymentService->simulatePayment($user, $orderId);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }
}
