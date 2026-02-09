<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\OnlineWorkAccount;
use App\Models\User;

class OnlineWorkController extends Controller
{
    // Display online work accounts
    public function index()
    {
        $user = Auth::user();
        
        $platforms = [];
        foreach (OnlineWorkAccount::getAllPlatforms() as $platform) {
            $platforms[$platform] = OnlineWorkAccount::getPlatformDetails($platform);
        }

        // Get user's active purchases
        $activeAccounts = OnlineWorkAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_name' => $account->platform_name,
                    'amount' => $account->amount,
                    'status' => $account->status,
                    'purchased_at' => $account->purchased_at?->format('M d, Y'),
                    'expires_at' => $account->expires_at?->format('M d, Y'),
                    'days_remaining' => $account->expires_at ? $account->expires_at->diffInDays(now()) : null,
                    'account_details' => $account->account_details,
                    'login_credentials' => $account->login_credentials,
                    'is_active' => $account->isActive(),
                    'is_expired' => $account->isExpired(),
                ];
            });

        return Inertia::render('OnlineWork/Index', [
            'platforms' => $platforms,
            'activeAccounts' => $activeAccounts,
            'userBalance' => $user->deposit_balance,
        ]);
    }

    // Purchase an online work account
    public function purchase(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:' . implode(',', OnlineWorkAccount::getAllPlatforms()),
        ]);

        $user = Auth::user();
        $platform = $request->platform;
        $platformDetails = OnlineWorkAccount::getPlatformDetails($platform);

        if (!$platformDetails) {
            return back()->with([
                'flash' => [
                    'error' => 'Invalid platform selected.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $platformDetails['amount']) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($platformDetails['amount']) . ' to purchase this account.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $platform, $platformDetails) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $platformDetails['amount']);

                $commissionAmount = $platformDetails['amount'] * 0.80; // 80% commission

                // Distribute commission to upline
                $uplineId = $user->referred_by;
                if ($uplineId) {
                    $upline = User::find($uplineId);
                    if ($upline) {
                        $upline->increment('deposit_balance', $commissionAmount);
                    }
                }

                // Generate account credentials
                $accountId = OnlineWorkAccount::generateAccountId($platform, $user->id);
                
                // Sample credentials (in real app, these would be generated or assigned)
                $loginCredentials = [
                    'account_id' => $accountId,
                    'email' => "user_{$user->id}_{$platform}@gmail.com",
                    'username' => "user_" . str_replace(' ', '_', strtolower($user->name)) . "_" . $platform,
                    'password' => bin2hex(random_bytes(8)), // Random password
                    'login_url' => $this->getPlatformLoginUrl($platform),
                    'instructions' => $this->getPlatformInstructions($platform),
                ];

                // Create account purchase record
                $account = OnlineWorkAccount::create([
                    'user_id' => $user->id,
                    'platform' => $platform,
                    'platform_name' => $platformDetails['name'],
                    'amount' => $platformDetails['amount'],
                    'account_details' => $platformDetails,
                    'login_credentials' => $loginCredentials,
                    'status' => 'active',
                    'purchased_at' => now(),
                    'expires_at' => now()->addDays($platformDetails['duration_days']),
                    'notes' => 'Account purchased successfully',
                ]);
            });

            return back()->with([
                'flash' => [
                    'success' => 'Account purchased successfully! Your ' . $platformDetails['name'] . ' is now active. Check your account details.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Online work account purchase failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to purchase account. Please try again.'
                ]
            ]);
        }
    }

    // Show purchase history
    public function history()
    {
        $user = Auth::user();
        
        $accounts = OnlineWorkAccount::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($account) {
                return [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_name' => $account->platform_name,
                    'amount' => $account->amount,
                    'status' => $account->status,
                    'purchased_at' => $account->purchased_at?->format('M d, Y H:i'),
                    'expires_at' => $account->expires_at?->format('M d, Y H:i'),
                    'created_at' => $account->created_at->format('M d, Y H:i'),
                    'account_details' => $account->account_details,
                    'login_credentials' => $account->login_credentials,
                    'is_active' => $account->isActive(),
                    'is_expired' => $account->isExpired(),
                    'days_remaining' => $account->expires_at ? $account->expires_at->diffInDays(now()) : null,
                ];
            });

        return Inertia::render('OnlineWork/History', [
            'accounts' => $accounts,
        ]);
    }

    // Show specific account details
    public function show($id)
    {
        $account = OnlineWorkAccount::where('user_id', Auth::id())
            ->findOrFail($id);

        return Inertia::render('OnlineWork/Show', [
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'platform_name' => $account->platform_name,
                'amount' => $account->amount,
                'status' => $account->status,
                'purchased_at' => $account->purchased_at?->format('M d, Y H:i'),
                'expires_at' => $account->expires_at?->format('M d, Y H:i'),
                'created_at' => $account->created_at->format('M d, Y H:i'),
                'account_details' => $account->account_details,
                'login_credentials' => $account->login_credentials,
                'notes' => $account->notes,
                'is_active' => $account->isActive(),
                'is_expired' => $account->isExpired(),
                'days_remaining' => $account->expires_at ? $account->expires_at->diffInDays(now()) : null,
            ],
        ]);
    }

    // Renew account
    public function renew($id)
    {
        $account = OnlineWorkAccount::where('user_id', Auth::id())
            ->findOrFail($id);

        $user = Auth::user();
        $platformDetails = $account->account_details;

        if ($user->deposit_balance < $platformDetails['amount']) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($platformDetails['amount']) . ' to renew this account.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $account, $platformDetails) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $platformDetails['amount']);

                // Update account expiry
                $newExpiry = $account->expires_at && $account->expires_at->isFuture()
                    ? $account->expires_at->addDays($platformDetails['duration_days'])
                    : now()->addDays($platformDetails['duration_days']);

                $account->update([
                    'status' => 'active',
                    'expires_at' => $newExpiry,
                    'notes' => $account->notes . "\nRenewed on " . now()->format('Y-m-d H:i:s'),
                ]);
            });

            return back()->with([
                'flash' => [
                    'success' => 'Account renewed successfully!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Account renewal failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to renew account. Please try again.'
                ]
            ]);
        }
    }

    // Get platform login URL
    private function getPlatformLoginUrl($platform)
    {
        $urls = [
            'handshake' => 'https://app.joinhandshake.com/login',
            'telus' => 'https://www.telusinternational.ai/login',
            'imerit' => 'https://platform.imerit.net/login',
            'atlas_capture' => 'https://atlas.mapseed.org/login',
            'crowdgen' => 'https://crowdgen.com/login',
            'mindrift' => 'https://mindrift.com/login',
            'dataannotation' => 'https://dataannotation.tech/login',
            'cloudworkers' => 'https://cloudworkers.com/login',
            'textfactory' => 'https://textfactory.com/login',
        ];

        return $urls[$platform] ?? '#';
    }

    // Get platform instructions
    private function getPlatformInstructions($platform)
    {
        $instructions = [
            'handshake' => 'Use the provided email and password to login. Complete your profile setup to start receiving freelance offers.',
            'telus' => 'Login with the credentials provided. Complete any pending tests to unlock more projects.',
            'imerit' => 'Access your account using the provided details. Start with training modules before taking on paid tasks.',
            'atlas_capture' => 'Download the mobile app and login. Follow the mapping instructions carefully.',
            'crowdgen' => 'Login to view available tasks. Start with simple tasks to build your rating.',
            'mindrift' => 'Use the credentials to access creative tasks. Build your portfolio with completed work.',
            'dataannotation' => 'Login to access AI training tasks. Focus on quality for higher earnings.',
            'cloudworkers' => 'Access remote tasks with your account. Check for new projects daily.',
            'textfactory' => 'Login to start text processing tasks. Follow quality guidelines carefully.',
        ];

        return $instructions[$platform] ?? 'Login with the provided credentials and start working.';
    }
}