<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSoftsaleViewData;
use App\Models\Affiliate;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientAuthService;
use App\Services\LandingContentService;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use BuildsSoftsaleViewData;

    public function __construct(private LandingContentService $landingContent) {}

    public function admin(): View
    {
        $users = User::query()
            ->with([
                'client.applications:id,name',
                'affiliate:id,user_id,code',
            ])
            ->latest('id')
            ->get();

        $affiliates = Affiliate::query()
            ->with(['user:id,name,email'])
            ->latest()
            ->get();

        $landingData = $this->softsaleLandingData($this->landingContent, 'admin');
        $landingData['adminOverview'] = [
            'clients' => User::query()->where('role', 'client')->count(),
            'affiliates_active' => Affiliate::query()->where('status', 'active')->count(),
        ];

        return view('dashboard.admin', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'users' => $users,
                'affiliates' => $affiliates,
                'landingData' => $landingData,
            ],
        ));
    }

    public function client(ClientAuthService $clientAuth, LicenseService $licenseService): View
    {
        $landingData = $this->softsaleLandingData($this->landingContent, 'client');
        $user = Auth::user();

        if ($user && $clientAuth->canAccessClientPortal($user)) {
            $landingData['initialClient'] = $clientAuth->clientPayload($user);
            $landingData['initialClientLicense'] = $licenseService->listForUser($user);
        }

        return view('dashboard.client', array_merge(
            $this->softsaleSiteData($this->landingContent),
            [
                'landingData' => $landingData,
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
