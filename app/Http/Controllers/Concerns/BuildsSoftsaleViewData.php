<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Application;
use App\Services\LandingContentService;

trait BuildsSoftsaleViewData
{
    protected function softsaleSiteData(LandingContentService $landingContent): array
    {
        $cms = $landingContent->all();

        return [
            'site' => $cms['site'],
            'cms' => $cms,
        ];
    }

    protected function softsaleLandingData(
        LandingContentService $landingContent,
        string $appMode,
        ?Application $application = null,
        bool $atHomeRoot = true,
    ): array {
        $payload = $application
            ? $landingContent->landingDataPayloadForApplication($application)
            : $landingContent->landingDataPayload();

        return array_merge($payload, [
            'appMode' => $appMode,
            'atHomeRoot' => $atHomeRoot,
            'landingUrl' => route('landing'),
            'adminDashboardUrl' => route('dashboard.admin'),
            'clientDashboardUrl' => route('dashboard.client'),
            'affiliateDashboardUrl' => route('dashboard.affiliate'),
            'referralUrl' => rtrim(config('softsale.referral_base_url'), '/').'/?ref=AFF001',
            'registerClientUrl' => route('register.client'),
            'verificationResendUrl' => route('verification.resend'),
            'loginClientUrl' => route('login.client'),
            'logoutUrl' => route('logout'),
            'clientMeUrl' => route('client.me'),
            'clientLicenseUrl' => route('client.license'),
            'clientLicenseDeactivateUrl' => route('client.license.deactivate'),
            'clientTransactionsUrl' => route('client.transactions'),
            'adminClientsUrl' => route('admin.clients.index'),
            'adminAffiliatesUrl' => route('admin.affiliates.index'),
            'adminCommissionsUrl' => route('admin.commissions.index'),
            'adminApplicationsUrl' => route('admin.applications.index'),
            'adminLicensesUrl' => route('admin.licenses.index'),
            'adminTransactionsUrl' => route('admin.transactions.index'),
            'adminPaymentOrdersUrl' => route('admin.payment-orders.index'),
            'adminLandingContentUrl' => route('admin.landing-content.index'),
            'adminLandingImageUploadUrl' => route('admin.landing-content.upload-image'),
            'adminHomepageSettingsUrl' => route('admin.homepage-settings.show'),
            'adminHubContentUrl' => route('admin.hub-content.index'),
            'adminMailSettingsUrl' => route('admin.mail-settings.show'),
            'adminMailSettingsTestUrl' => route('admin.mail-settings.test'),
            'liveEditUrl' => $application
                ? route('landing.application', ['slug' => $application->slug, 'live_edit' => 1])
                : route('landing', ['live_edit' => 1]),
        ]);
    }
}
