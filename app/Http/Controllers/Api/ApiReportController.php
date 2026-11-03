<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportArchivePasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiReportController extends Controller
{
    public function __construct(private ReportArchivePasswordService $reportArchive) {}

    public function archivePassword(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');

        $applicationSlug = trim((string) (
            $request->query('application_slug')
            ?? $request->input('application_slug')
            ?? 'spot-color'
        ));

        if (! $this->reportArchive->isConfigured($user, $applicationSlug)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password arsip laporan belum diatur untuk aplikasi ini. Atur di dashboard client → Lisensi → Password Laporan.',
                'configured' => false,
            ], 400);
        }

        $password = $this->reportArchive->passwordForUser($user, $applicationSlug);
        if ($password === null || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Password arsip tidak dapat dibaca. Atur ulang di dashboard client.',
                'configured' => false,
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'configured' => true,
            'password' => $password,
        ]);
    }
}
