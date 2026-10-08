<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLandingContentRequest;
use App\Services\LandingContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif,svg', 'max:5120'],
        ]);

        $directory = public_path('images/landing/features');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = Str::uuid().'.'.$extension;
        $file->move($directory, $filename);

        return response()->json([
            'url' => '/images/landing/features/'.$filename,
        ]);
    }
}
