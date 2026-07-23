<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $clients = Client::query()
            ->with(['user:id,name,email', 'applications:id,name'])
            ->latest()
            ->get();

        return view('landing.home', [
            'site' => config('softsale'),
            'clients' => $clients,
            'landingData' => [
                'features' => config('softsale.features'),
                'plans' => config('softsale.plans'),
                'testimonials' => config('softsale.testimonials'),
                'aboutValues' => config('softsale.about_values'),
                'contacts' => config('softsale.contacts'),
                'referralUrl' => rtrim(config('softsale.referral_base_url'), '/').'/?ref=AFF001',
                'registerClientUrl' => route('register.client'),
                'loginClientUrl' => route('login.client'),
                'logoutUrl' => route('logout'),
                'clientMeUrl' => route('client.me'),
                'clientLicenseUrl' => route('client.license'),
                'clientLicenseDeactivateUrl' => route('client.license.deactivate'),
                'clientTransactionsUrl' => route('client.transactions'),
                'adminClientsUrl' => route('admin.clients.index'),
                'adminApplicationsUrl' => route('admin.applications.index'),
                'adminLicensesUrl' => route('admin.licenses.index'),
                'adminTransactionsUrl' => route('admin.transactions.index'),
                'adminPaymentOrdersUrl' => route('admin.payment-orders.index'),
            ],
        ]);
    }
}
