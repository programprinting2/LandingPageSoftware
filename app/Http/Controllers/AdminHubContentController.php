<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHubContentRequest;
use App\Services\HubContentService;
use Illuminate\Http\JsonResponse;

class AdminHubContentController extends Controller
{
    public function __construct(private HubContentService $hubContent) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'sections' => $this->hubContent->all(),
            'keys' => HubContentService::SECTIONS,
        ]);
    }

    public function update(UpdateHubContentRequest $request, string $key): JsonResponse
    {
        if (! in_array($key, HubContentService::SECTIONS, true)) {
            return response()->json(['message' => 'Section hub tidak dikenali.'], 404);
        }

        $payload = $request->validated()['payload'];

        $section = $this->hubContent->updateSection($key, $payload);

        return response()->json([
            'message' => 'Konten portal berhasil disimpan.',
            'section' => $section,
            'key' => $key,
        ]);
    }
}
