<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Application;
use App\Services\LandingContentService;

trait BuildsSoftsaleViewData
{
    protected function softsaleSiteData(LandingContentService $landingContent, bool $minimal = false): array
    {
        $cms = $landingContent->all();

        if ($minimal) {
            return [
                'site' => $cms['site'],
                'cms' => ['site' => $cms['site']],
            ];
        }

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
        bool $includeMarketingPayload = true,
    ): array {
        if ($includeMarketingPayload) {
            $payload = $application
                ? $landingContent->landingDataPayloadForApplication($application)
                : $landingContent->landingDataPayload();
        } else {
            $cms = $landingContent->all();
            $payload = [
                'site' => $cms['site'],
            ];
        }

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
            'loginAdminUrl' => route('login.admin'),
            'adminMeUrl' => route('admin.me'),
            'logoutUrl' => route('logout'),
            'adminUsersUrl' => route('admin.users.index'),
            'adminUsersRoleUrl' => url('/api/admin/users'),
            'clientMeUrl' => route('client.me'),
            'clientLicenseUrl' => route('client.license'),
            'clientCheckoutUrl' => route('client.checkout'),
            'clientDeviceSlotsCheckoutUrl' => route('client.checkout.device-slots'),
            'clientCommercePlansUrl' => route('client.commerce.plans'),
            'clientTrialActivateUrl' => route('client.trial.activate'),
            'clientCheckoutStatusUrl' => url('/api/client/checkout'),
            'clientCheckoutSimulateUrl' => url('/api/client/checkout'),
            'clientLicenseDeactivateUrl' => route('client.license.deactivate'),
            'clientTransactionsUrl' => route('client.transactions'),
            'clientAffiliateUrl' => route('client.affiliate'),
            'clientAffiliateEnrollUrl' => route('client.affiliate.enroll'),
            'clientAffiliateCodeUrl' => route('client.affiliate.code.update'),
            'adminUsersAffiliateUrl' => url('/api/admin/users'),
            'adminUsersDestroyUrl' => url('/api/admin/users'),
            'adminClientsUrl' => route('admin.clients.index'),
            'adminAffiliatesUrl' => route('admin.affiliates.index'),
            'adminPromoVouchersUrl' => route('admin.promo-vouchers.index'),
            'adminCommissionsUrl' => route('admin.commissions.index'),
            'adminApplicationsUrl' => route('admin.applications.index'),
            'adminApplicationLogoUploadUrl' => route('admin.applications.upload-logo'),
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
