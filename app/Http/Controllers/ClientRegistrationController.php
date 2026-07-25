<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRegistrationRequest;
use App\Services\ClientAuthService;
use Illuminate\Http\JsonResponse;

class ClientRegistrationController extends Controller
{
    public function __construct(private ClientAuthService $authService) {}

    public function store(StoreClientRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->registerClient([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'company_name' => $validated['company_name'],
            'whatsapp' => $validated['whatsapp'],
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil. Kami telah mengirim link konfirmasi ke email Anda. Silakan verifikasi sebelum login.',
            'requires_verification' => true,
            'email' => $result['user']->email,
        ], 201);
    }
}
