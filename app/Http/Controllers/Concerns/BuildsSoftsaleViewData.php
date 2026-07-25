<?php

namespace App\Http\Controllers\Concerns;

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

    protected function softsaleLandingData(LandingContentService $landingContent, string $appMode): array
    {
        return array_merge($landingContent->landingDataPayload(), [
            'appMode' => $appMode,
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
            'adminMailSettingsUrl' => route('admin.mail-settings.show'),
            'adminMailSettingsTestUrl' => route('admin.mail-settings.test'),
            'liveEditUrl' => route('landing', ['live_edit' => 1]),
        ]);
    }
}
