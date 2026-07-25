<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLandingContentRequest;
use App\Services\LandingContentService;
use Illuminate\Http\JsonResponse;

class AdminLandingContentController extends Controller
{
    public function __construct(private LandingContentService $landingContent) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'sections' => $this->landingContent->all(),
            'keys' => LandingContentService::SECTIONS,
        ]);
    }

    public function update(UpdateLandingContentRequest $request, string $key): JsonResponse
    {
        if (! in_array($key, LandingContentService::SECTIONS, true)) {
            return response()->json([
                'message' => 'Section CMS tidak dikenali.',
            ], 404);
        }

        $payload = $request->validated()['payload'];
        $section = $this->landingContent->updateSection($key, $payload);

        return response()->json([
            'message' => 'Konten halaman utama berhasil disimpan.',
            'section' => $section,
            'key' => $key,
        ]);
    }
}
