<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSoftsaleViewData;
use App\Services\LandingContentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    use BuildsSoftsaleViewData;

    public function __construct(private LandingContentService $landingContent) {}

    public function index(Request $request): View
    {
        $liveEdit = $request->boolean('live_edit');
        $landingData = $this->softsaleLandingData($this->landingContent, 'landing');

        if ($liveEdit) {
            $landingData['liveEdit'] = true;
            $landingData['cmsSections'] = $this->landingContent->all();
        }

        return view('landing.home', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'liveEdit' => $liveEdit,
                'landingData' => $landingData,
            ],
        ));
    }
}
