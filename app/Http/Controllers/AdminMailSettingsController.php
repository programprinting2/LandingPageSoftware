<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailTestRequest;
use App\Http\Requests\UpdateMailSettingsRequest;
use App\Services\MailSettingsService;
use Illuminate\Http\JsonResponse;

class AdminMailSettingsController extends Controller
{
    public function __construct(private MailSettingsService $mailSettings) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'settings' => $this->mailSettings->getForAdmin(),
        ]);
    }

    public function update(UpdateMailSettingsRequest $request): JsonResponse
    {
        $settings = $this->mailSettings->update($request->validated());

        return response()->json([
            'message' => 'Pengaturan email berhasil disimpan.',
            'settings' => $settings,
        ]);
    }

    public function sendTest(SendMailTestRequest $request): JsonResponse
    {
        try {
            $this->mailSettings->sendTestEmail($request->validated('email'));

            return response()->json([
                'message' => 'Email tes berhasil dikirim. Periksa inbox (atau log jika driver log).',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal mengirim email tes: '.$e->getMessage(),
            ], 422);
        }
    }
}
