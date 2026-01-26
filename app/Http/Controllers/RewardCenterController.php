<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\BonusClaim;

class RewardCenterController extends Controller
{
    private $bonusTypes = [
        'welcome_bonus' => [
            'name' => 'Welcome Bonus',
            'bonus_amount' => 5000,
            'claim_cost' => 1000,
            'description' => 'Claim KES. 5,000 for KES. 1,000',
            'route' => 'reward-center.index'
        ],
        'minor_bonus' => [
            'name' => 'Minor Bonus',
            'bonus_amount' => 5000,
            'claim_cost' => 1000,
            'description' => 'Claim KES. 5,000 for KES. 1,000',
            'route' => 'reward-center.purchase'
        ],
        'pro_bonus' => [
            'name' => 'Pro Bonus',
            'bonus_amount' => 10000,
            'claim_cost' => 2000,
            'description' => 'Claim KES. 10,000 for KES. 2,000',
            'route' => 'reward-center.stats'
        ],
        'mega_bonus' => [
            'name' => 'Mega Bonus',
            'bonus_amount' => 50000,
            'claim_cost' => 4000,
            'description' => 'Claim KES. 50,000 for KES. 4,000',
            'route' => 'reward-center.stats'
        ]
    ];

    public function index(Request $request)
    {
        return $this->renderBonusView('welcome_bonus', 'RewardCenter/WelcomeBonus', $request);
    }

    public function purchase(Request $request)
    {
        return $this->renderBonusView('minor_bonus', 'RewardCenter/MinorBonus', $request);
    }

    public function stats(Request $request)
    {

        // dd($request->all());
        // Determine which bonus based on URL
        $isMegaBonus = $request->is('reward-center/mega-bonus');
        $bonusType = $isMegaBonus ? 'mega_bonus' : 'pro_bonus';
        $viewName = $isMegaBonus ? 'RewardCenter/MegaBonus' : 'RewardCenter/ProBonus';

        // dd($bonusType);
        
        return $this->renderBonusView($bonusType, $viewName, $request);
    }

    private function renderBonusView($bonusType, $viewName, Request $request)
    {
        $bonusInfo = $this->bonusTypes[$bonusType];
        $hasClaimed = BonusClaim::hasClaimed(Auth::id(), $bonusType);
        $userBalance = Auth::user()->deposit_balance;

        // dd($bonusType);

        if ($request->isMethod('post')) {
            // dd($bonusType);
            return $this->processBonusClaim($request, $bonusType);
        }

        // dd($bonusInfo, $hasClaimed, $userBalance);
        return Inertia::render($viewName, [
            'bonusInfo' => $bonusInfo,
            'hasClaimed' => $hasClaimed,
            'userBalance' => $userBalance
        ]);
    }

    private function processBonusClaim(Request $request, $bonusType)
    {

        // dd($bonusType);
        $bonusInfo = $this->bonusTypes[$bonusType];
        $user = Auth::user();

        // Check if already claimed
        if (BonusClaim::hasClaimed($user->id, $bonusType)) {
            return redirect()->back()->with([
                'flash' => [
                    'error' => 'You have already claimed this bonus!'
                ]
            ]);
        }

        // Check balance
        if (!$user->hasSufficientBalance($bonusInfo['claim_cost'])) {
            return redirect()->back()->with([
                'flash' => [
                    'error' => 'Insufficient balance! You need KES ' . number_format($bonusInfo['claim_cost']) . ' to claim this bonus.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $bonusType, $bonusInfo) {
                // Calculate net amount (bonus - cost)
                $netAmount = $bonusInfo['bonus_amount'] - $bonusInfo['claim_cost'];
                
                // Deduct the claim cost from balance
                $user->decrement('deposit_balance', $bonusInfo['claim_cost']);
                
                // Record the bonus claim
                BonusClaim::create([
                    'user_id' => $user->id,
                    'bonus_type' => $bonusType,
                    'bonus_amount' => $bonusInfo['bonus_amount'],
                    'claim_cost' => $bonusInfo['claim_cost'],
                    'status' => 'claimed'
                ]);
            });

            return redirect()->back()->with([
                'flash' => [
                    'success' => 'Bonus claimed successfully! Your net gain is KES ' . number_format($bonusInfo['bonus_amount'] - $bonusInfo['claim_cost']) . ' added to your balance.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Bonus claim failed: ' . $e->getMessage());
            return redirect()->back()->with([
                'flash' => [
                    'error' => 'Failed to claim bonus. Please try again.'
                ]
            ]);
        }
    }

    public function getBonusHistory()
    {
        $bonusHistory = BonusClaim::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('RewardCenter/BonusHistory', [
            'bonusHistory' => $bonusHistory
        ]);
    }
}