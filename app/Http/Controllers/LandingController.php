<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSoftsaleViewData;
use App\Models\Application;
use App\Services\HomepageSettingsService;
use App\Services\HubCatalogService;
use App\Services\HubContentService;
use App\Services\LandingContentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    use BuildsSoftsaleViewData;

    public function __construct(
        private LandingContentService $landingContent,
        private HomepageSettingsService $homepageSettings,
        private HubContentService $hubContent,
        private HubCatalogService $hubCatalog,
    ) {}

    public function index(Request $request): View
    {
        $settings = $this->homepageSettings->get();

        if ($settings['mode'] === HomepageSettingsService::MODE_DEFAULT) {
            return $this->hub($request);
        }

        $application = $this->homepageSettings->homepageApplication();
        if (! $application) {
            return $this->hub($request);
        }

        return $this->applicationLanding($request, $application, atHomeRoot: true);
    }

    public function application(Request $request, string $slug): View
    {
        $application = Application::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->applicationLanding($request, $application, atHomeRoot: false);
    }

    private function hub(Request $request): View
    {
        $applications = Application::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        $landingData = array_merge(
            $this->softsaleLandingData($this->landingContent, 'hub'),
            [
                'hubApplications' => $applications->map(fn ($app) => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'slug' => $app->slug,
                    'description' => $app->description,
                    'url' => route('landing.application', $app->slug),
                ])->values()->all(),
            ],
        );

        $hub = $this->hubContent->viewPayload();

        $catalogItems = $this->hubCatalog->buildItems($applications);

        return view('hub.home', [
            'applications' => $applications,
            'catalogItems' => $catalogItems,
            'hub' => $hub,
            'site' => [
                'name' => $hub['site']['brand'] ?? config('softsale.name'),
                'tagline' => $hub['site']['tagline'] ?? config('softsale.tagline'),
            ],
            'landingData' => $landingData,
        ]);
    }

    private function applicationLanding(Request $request, Application $application, bool $atHomeRoot): View
    {
        $liveEdit = $request->boolean('live_edit');
        $landingData = $this->softsaleLandingData($this->landingContent, 'landing', $application, $atHomeRoot);

        if ($liveEdit) {
            $landingData['liveEdit'] = true;
            $landingData['cmsSections'] = $this->landingContent->all($application->id);
            $landingData['cmsApplicationId'] = $application->id;
        }

        return view('landing.home', array_merge(
            $this->softsaleSiteDataForApplication($application),
            [
                'liveEdit' => $liveEdit,
                'landingData' => $landingData,
            ],
        ));
    }

    /**
     * @return array{site: array<string, mixed>, cms: array<string, mixed>}
     */
    private function softsaleSiteDataForApplication(Application $application): array
    {
        $cms = $this->landingContent->all($application->id);

        return [
            'site' => $cms['site'],
            'cms' => $cms,
        ];
    }
}
