<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', [
            'site' => config('softsale'),
            'landingData' => [
                'features' => config('softsale.features'),
                'plans' => config('softsale.plans'),
                'testimonials' => config('softsale.testimonials'),
                'aboutValues' => config('softsale.about_values'),
                'contacts' => config('softsale.contacts'),
                'referralUrl' => rtrim(config('softsale.referral_base_url'), '/').'/?ref=AFF001',
            ],
        ]);
    }
}
