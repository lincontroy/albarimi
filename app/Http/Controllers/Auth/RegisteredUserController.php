<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use App\Mail\WelcomeEmail; // Add this
use Illuminate\Support\Facades\Mail; // Add this

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $referralCode = $request->query('ref');
        $referrer = null;
        
        if ($referralCode) {
            $referrer = User::where('id', $referralCode)->first();
        }

        return Inertia::render('Auth/Register', [
            'referral_code' => $referralCode,
            'referrer' => $referrer ? [
                'name' => $referrer->name,
                'is_agent' => $referrer->is_agent,
            ] : null,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $referralCode = $request->referral_code;

        // dd($referralCode);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'referral_code' => 'nullable|string',
        ]);

        // Start creating user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'referral_code' => $this->generateUniqueReferralCode(),
          
            'deposit_balance' => 0,
            'is_agent' => false,
            'last_active_at' => now(),
        ];
        // dd($request->all());
       

        // Handle referral if provided
      
        if ($referralCode) {
            $referrer = User::where('id', $referralCode)->first();
            if ($referrer) {
                $userData['referred_by'] = $referralCode;
            }
        }

        $user = User::create($userData);

        // Send welcome email
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        // Update referrer's stats if referral was used
        if ($user->referred_by) {
            $referrer = User::find($user->referred_by);
            if ($referrer) {
                $referrer->increment('referral_count');
                $referrer->increment('active_referral_count');
                
                // Award referral bonus to referrer (optional)
                $referrerBonus = 100; // KES 100 bonus for each referral
                $referrer->increment('deposit_balance', $referrerBonus);
                $referrer->increment('total_earned_from_referrals', $referrerBonus);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))->with([
            'flash' => [
                'success' => 'Registration successful! Welcome to Barimax Top. Check your email for welcome details.'
            ]
        ]);
    }

    /**
     * Generate a unique referral code
     */
    private function generateUniqueReferralCode(): string
    {
       
        
      
            // Generate 8-character alphanumeric code
        $code = strtoupper(Str::random(8));
    
        return $code;
    }
}