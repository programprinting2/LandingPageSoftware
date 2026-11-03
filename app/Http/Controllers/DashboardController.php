<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSoftsaleViewData;
use App\Models\Affiliate;
use App\Models\User;
use App\Services\AdminAuthService;
use App\Services\ClientAuthService;
use App\Services\LandingContentService;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use BuildsSoftsaleViewData;

    public function __construct(private LandingContentService $landingContent) {}

    public function admin(AdminAuthService $adminAuth): View
    {
        $landingData = $this->softsaleLandingData(
            $this->landingContent,
            'admin',
            includeMarketingPayload: false,
        );
        $landingData['adminOverview'] = [
            'clients' => User::query()->where('role', 'client')->count(),
            'affiliates_active' => Affiliate::query()->where('status', 'active')->count(),
        ];

        $user = Auth::user();
        if ($user && in_array($user->role, AdminAuthService::ADMIN_ROLES, true)) {
            $landingData['initialAdmin'] = $adminAuth->adminPayload($user);
        }

        return view('dashboard.admin', array_merge(
            $this->softsaleSiteData($this->landingContent, minimal: true),
            [
                'users' => collect(),
                'affiliates' => collect(),
                'landingData' => $landingData,
            ],
        ));
    }

    public function client(ClientAuthService $clientAuth, LicenseService $licenseService): View
    {
        $landingData = $this->softsaleLandingData(
            $this->landingContent,
            'client',
            includeMarketingPayload: false,
        );
        $user = Auth::user();

        if ($user && $clientAuth->canAccessClientPortal($user)) {
            $landingData['initialClient'] = $clientAuth->clientPayload($user);
            $landingData['initialClientLicense'] = $licenseService->listForUser($user);
        }

        return view('dashboard.client', array_merge(
            $this->softsaleSiteData($this->landingContent, minimal: true),
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
