<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KomerceWebhookController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function paymentNotification(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Callback-Api-Key');

        $result = $this->paymentService->handleWebhook($rawBody, $signature);

        return response()->json($result['body'], $result['http_status'] ?? 200);
    }
}
