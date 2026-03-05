<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppWithdrawal;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WhatsAppWithdrawalController extends Controller
{
    /**
     * Display the WhatsApp withdrawals page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has Bariplus Package
        $hasBariplus = $user->package === 'Bariplus Package';
        
        // Get user's withdrawals
        $withdrawals = WhatsAppWithdrawal::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'whatsapp_number' => $withdrawal->whatsapp_number,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'transaction_id' => $withdrawal->transaction_id,
                    'user_notes' => $withdrawal->user_notes,
                    'requested_at' => $withdrawal->requested_at->toISOString(),
                    'processed_at' => $withdrawal->processed_at?->toISOString(),
                    'created_at' => $withdrawal->created_at->toISOString(),
                ];
            });

        // Calculate stats
        $stats = [
            'total_withdrawn' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'approved', 'processing'])
                ->sum('amount'),
            'pending_withdrawals' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'completed_withdrawals' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'available_balance' => $user->whatsapp_balance ?? 0,
        ];

        return Inertia::render('SubmitViews/Withdrawal', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'limits' => [
                'min' => WhatsAppWithdrawal::MIN_WITHDRAWAL,
                'max' => WhatsAppWithdrawal::MAX_WITHDRAWAL,
            ],
            'hasBariplus' => $hasBariplus,
            'userPackage' => $user->package,
        ]);
    }

    /**
     * Store a new withdrawal request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user has Bariplus Package
        if ($user->package !== 'Bariplus Package') {
            return response()->json([
                'message' => 'Please upgrade to Bariplus Package to access WhatsApp withdrawals.',
                'errors' => ['package' => 'You need to upgrade to Bariplus Package to access WhatsApp withdrawals.'],
                'requires_upgrade' => true
            ], 403);
        }

        $request->validate([
            'whatsapp_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'amount' => [
                'required',
                'numeric',
                'min:' . WhatsAppWithdrawal::MIN_WITHDRAWAL,
                'max:' . WhatsAppWithdrawal::MAX_WITHDRAWAL,
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Check if user has sufficient balance
            $availableBalance = $user->whatsapp_balance ?? 0;
            if ($availableBalance < $request->amount) {
                return response()->json([
                    'message' => 'Insufficient balance for withdrawal.',
                    'errors' => ['amount' => 'You only have KES ' . number_format($availableBalance) . ' available.']
                ], 422);
            }

            // Create withdrawal
            $withdrawal = WhatsAppWithdrawal::create([
                'user_id' => $user->id,
                'whatsapp_number' => $request->whatsapp_number,
                'amount' => $request->amount,
                'user_notes' => $request->notes,
                'status' => WhatsAppWithdrawal::STATUS_COMPLETED, // Changed from COMPLETED to PENDING
                'requested_at' => now(),
            ]);

            $user->decrement('whatsapp_balance', $request->amount);

            
            $user->save();


            // Generate transaction ID
            $withdrawal->update([
                'transaction_id' => $withdrawal->generateTransactionId()
            ]);

            $message = "Dear {$user->name} you have successfuly withdrawn KES " . number_format($withdrawal->amount) . ". The amount will be credited to your account.";
            // $this->notifyAdmin($message);

            return response()->json([
                'message' => $message,
                'withdrawal' => [
                    'id' => $withdrawal->id,
                    'transaction_id' => $withdrawal->transaction_id,
                    'amount' => $withdrawal->amount,
                    'whatsapp_number' => $withdrawal->whatsapp_number,
                    'status' => $withdrawal->status,
                    'requested_at' => $withdrawal->requested_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating withdrawal: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error submitting withdrawal request. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal history
     */
    public function history()
    {
        $withdrawals = WhatsAppWithdrawal::where('user_id', Auth::id())
            ->latest()
            ->paginate(15)
            ->through(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'whatsapp_number' => $withdrawal->whatsapp_number,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'transaction_id' => $withdrawal->transaction_id,
                    'user_notes' => $withdrawal->user_notes,
                    'admin_notes' => $withdrawal->admin_notes,
                    'requested_at' => $withdrawal->requested_at->toISOString(),
                    'processed_at' => $withdrawal->processed_at?->toISOString(),
                    'completed_at' => $withdrawal->completed_at?->toISOString(),
                    'created_at' => $withdrawal->created_at->toISOString(),
                ];
            });

        return Inertia::render('SubmitViews/History', [
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Get withdrawal statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_withdrawn' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'approved', 'processing'])
                ->sum('amount'),
            'pending_withdrawals' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'completed_withdrawals' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'failed_withdrawals' => WhatsAppWithdrawal::where('user_id', $user->id)
                ->where('status', 'failed')
                ->count(),
            'available_balance' => $user->whatsapp_balance ?? 0,
            'total_requests' => WhatsAppWithdrawal::where('user_id', $user->id)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Show a specific withdrawal
     */
    public function show(WhatsAppWithdrawal $withdrawal)
    {
        // Authorization - user can only view their own withdrawals
        if ($withdrawal->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('CashFlow/WhatsappWithdrawals/Show', [
            'withdrawal' => [
                'id' => $withdrawal->id,
                'whatsapp_number' => $withdrawal->whatsapp_number,
                'amount' => $withdrawal->amount,
                'status' => $withdrawal->status,
                'transaction_id' => $withdrawal->transaction_id,
                'user_notes' => $withdrawal->user_notes,
                'admin_notes' => $withdrawal->admin_notes,
                'requested_at' => $withdrawal->requested_at->toISOString(),
                'processed_at' => $withdrawal->processed_at?->toISOString(),
                'completed_at' => $withdrawal->completed_at?->toISOString(),
                'created_at' => $withdrawal->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Cancel a pending withdrawal
     */
    public function cancel(WhatsAppWithdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$withdrawal->isPending()) {
            return response()->json([
                'message' => 'Only pending withdrawals can be cancelled.'
            ], 422);
        }

        $withdrawal->update([
            'status' => WhatsAppWithdrawal::STATUS_REJECTED,
            'admin_notes' => 'Cancelled by user',
            'processed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Withdrawal cancelled successfully.'
        ]);
    }
}