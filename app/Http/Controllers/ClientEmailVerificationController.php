<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientEmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('landing', ['verified' => 'already']);
        }

        $request->fulfill();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.client');
    }

    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($validated['email'])))
            ->where('role', 'client')
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Jika email terdaftar, link verifikasi akan dikirim.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah diverifikasi. Silakan login.',
                'verified' => true,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link verifikasi telah dikirim ulang. Periksa inbox email Anda.',
        ]);
    }
}
