<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRegistrationRequest;
use App\Services\ClientAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        Auth::login($result['user']);

        return response()->json([
            'message' => 'Pendaftaran client berhasil.',
            'client' => $this->authService->clientPayload($result['user']),
        ], 201);
    }
}
