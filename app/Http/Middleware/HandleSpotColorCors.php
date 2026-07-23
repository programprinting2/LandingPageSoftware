<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSpotColorCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $origins = config('spotcolor.cors_origins', ['*']);
        $origin = $request->headers->get('Origin');
        $allowOrigin = '*';

        if ($origin && ($origins === ['*'] || in_array('*', $origins, true) || in_array($origin, $origins, true))) {
            $allowOrigin = $origin;
        } elseif ($origins !== ['*'] && ! in_array('*', $origins, true) && count($origins) > 0) {
            $allowOrigin = $origins[0];
        }

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-CSRF-TOKEN')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-CSRF-TOKEN');

        return $response;
    }
}
