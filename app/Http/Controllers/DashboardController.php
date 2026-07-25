<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSoftsaleViewData;
use App\Models\Affiliate;
use App\Models\Client;
use App\Services\LandingContentService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use BuildsSoftsaleViewData;

    public function __construct(private LandingContentService $landingContent) {}

    public function admin(): View
    {
        $clients = Client::query()
            ->with(['user:id,name,email,email_verified_at', 'applications:id,name'])
            ->latest()
            ->get();

        $affiliates = Affiliate::query()
            ->with(['user:id,name,email'])
            ->latest()
            ->get();

        return view('dashboard.admin', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'clients' => $clients,
                'affiliates' => $affiliates,
                'landingData' => $this->softsaleLandingData($this->landingContent, 'admin'),
            ],
        ));
    }

    public function client(): View
    {
        return view('dashboard.client', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'landingData' => $this->softsaleLandingData($this->landingContent, 'client'),
            ],
        ));
    }

    public function affiliate(): View
    {
        return view('dashboard.affiliate', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'landingData' => $this->softsaleLandingData($this->landingContent, 'affiliate'),
            ],
        ));
    }
}
