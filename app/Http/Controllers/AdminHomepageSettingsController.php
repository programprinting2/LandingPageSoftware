<?php

namespace App\Http\Controllers;

use App\Services\HomepageSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHomepageSettingsController extends Controller
{
    public function __construct(private HomepageSettingsService $homepageSettings) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'settings' => $this->homepageSettings->get(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:default,application'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
        ]);

        $settings = $this->homepageSettings->update($validated);

        return response()->json([
            'message' => 'Pengaturan homepage berhasil disimpan.',
            'settings' => $settings,
        ]);
    }
}
