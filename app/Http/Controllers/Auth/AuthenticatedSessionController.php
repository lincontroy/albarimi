<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Mail\LoginNotification; // Add this
use Illuminate\Support\Facades\Mail; // Add this
use Jenssegers\Agent\Agent; // Add this for device detection

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get user and device info
        $user = Auth::user();
        
        // Update last active timestamp
        $user->update(['last_active_at' => now()]);

        // Detect device information
        $agent = new Agent();
        $deviceInfo = [
            'device' => $agent->device() ?: 'Unknown Device',
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'location' => $this->getLocationFromIP($request->ip()),
        ];

        // Send login notification email
        try {
            Mail::to($user->email)->send(new LoginNotification($user, $deviceInfo));
        } catch (\Exception $e) {
            \Log::error('Failed to send login notification email: ' . $e->getMessage());
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get location information from IP (simplified version)
     */
    private function getLocationFromIP($ip)
    {
        // In production, use a proper IP geolocation service
        // This is a simplified example
        if ($ip === '127.0.0.1') {
            return 'Localhost';
        }
        
        // For demo purposes, return generic location
        // You can implement ipinfo.io, ip-api.com, or MaxMind GeoIP
        return 'Nairobi, Kenya';
    }
}