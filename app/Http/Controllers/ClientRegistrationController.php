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
            'whatsapp' => trim((string) ($validated['whatsapp'] ?? '')),
            'application_id' => $validated['application_id'] ?? null,
        ]);

        $user = $result['user'];
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Pendaftaran berhasil. Anda sudah masuk.',
            'logged_in' => true,
            'requires_verification' => true,
            'email' => $user->email,
            'client' => $this->authService->clientPayload($user),
        ], 201);
    }
}
