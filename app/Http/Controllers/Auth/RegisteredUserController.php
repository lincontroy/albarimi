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
            $referrer = User::where('username', $referralCode)->first();
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
            'username' => 'nullable|string|max:255|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'referral_code' => 'nullable|string',
        ]);

        // Start creating user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username ?? Str::slug($request->name) . '-' . Str::random(5),
            'password' => Hash::make($request->password),
            'password_text' => $request->password,
            'phone' => $this->cleanPhoneNumber($request->phone), // Just add this here
            'referral_code' => $this->generateUniqueReferralCode(),
          
            'deposit_balance' => 0,
            'is_agent' => false,
            'last_active_at' => now(),
        ];
        // dd($request->all());
       

        // Handle referral if provided
      
        if ($referralCode) {
            $referrer = User::where('username', $referralCode)->first();
            if ($referrer) {
                $id= $referrer->id;
                $userData['referred_by'] = $id;
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
                $referrerBonus = 0; // KES 100 bonus for each referral
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

    /**
 * Clean phone number to ensure it starts with 07 or 01
 * 
 * @param string|null $phone
 * @return string|null
 */
private function cleanPhoneNumber($phone)
{
    if (empty($phone)) {
        return null;
    }
    
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If it starts with 254 (Kenya country code)
    if (strlen($phone) >= 10 && substr($phone, 0, 3) === '254') {
        // Remove 254 and add 0 at the beginning
        return '0' . substr($phone, 3);
    }
    
    // If it's 9 digits and starts with 7 or 1 (missing leading 0)
    if (strlen($phone) === 9 && (substr($phone, 0, 1) === '7' || substr($phone, 0, 1) === '1')) {
        return '0' . $phone;
    }
    
    // If it's 10 digits and already starts with 07 or 01, return as is
    if (strlen($phone) === 10 && (substr($phone, 0, 2) === '07' || substr($phone, 0, 2) === '01')) {
        return $phone;
    }
    
    // If it's 10 digits and starts with 7 or 1 (without leading zero)
    if (strlen($phone) === 10 && (substr($phone, 0, 1) === '7' || substr($phone, 0, 1) === '1')) {
        return '0' . $phone;
    }
    
    // Return cleaned number if it doesn't match expected patterns
    // The validation will catch invalid formats
    return $phone;
}
}