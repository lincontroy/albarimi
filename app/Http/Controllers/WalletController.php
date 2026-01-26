<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WalletController extends Controller
{
    // Main wallet page
    public function index()
    {
        $user = Auth::user();
        
        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'type' => $transaction->type,
                    'type_badge' => $transaction->getTypeBadge(),
                    'amount' => $transaction->getFormattedAmount(),
                    'net_amount' => $transaction->getFormattedNetAmount(),
                    'fee' => $transaction->getFormattedFee(),
                    'status' => $transaction->status,
                    'status_badge' => $transaction->getStatusBadge(),
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->format('M d, Y H:i'),
                    'is_positive' => in_array($transaction->type, ['deposit', 'transfer_in', 'refund']),
                ];
            });

        // Stats
        $stats = [
            'total_deposits' => Transaction::where('user_id', $user->id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('net_amount'),
            'total_withdrawals' => Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('net_amount'),
            'total_transfers' => Transaction::where('user_id', $user->id)
                ->where('type', 'transfer')
                ->where('status', 'completed')
                ->sum('net_amount'),
            'pending_transactions' => Transaction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        return Inertia::render('Wallet/Index', [
            'balance' => $user->deposit_balance ?? 0,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
        ]);
    }

    // Deposit page
    public function deposit()
    {
        $user = Auth::user();
        
        return Inertia::render('Wallet/Deposit', [
            'balance' => $user->deposit_balance ?? 0,
            'min_deposit' => 100,
            'max_deposit' => 50000,
            'deposit_fee_percentage' => 1.5, // 1.5% fee
        ]);
    }

    // Process deposit
    public function processDeposit(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100|max:50000',
            'payment_method' => 'required|in:mpesa,bank,card',
            'phone_number' => 'required_if:payment_method,mpesa|regex:/^[0-9]{10,12}$/',
            'account_number' => 'required_if:payment_method,bank|string',
            'card_number' => 'required_if:payment_method,card|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Calculate fee (1.5%)
        $amount = $request->amount;
        $fee = $amount * 0.015; // 1.5% fee
        $netAmount = $amount - $fee;

        try {
            DB::transaction(function () use ($user, $request, $amount, $fee, $netAmount) {
                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'DEP-' . time() . '-' . strtoupper(uniqid()),
                    'type' => 'deposit',
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                    'balance_before' => $user->deposit_balance,
                    'balance_after' => $user->deposit_balance + $netAmount,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $this->generatePaymentReference($request->payment_method),
                    'description' => 'Deposit via ' . ucfirst($request->payment_method),
                    'metadata' => [
                        'phone_number' => $request->phone_number ?? null,
                        'account_number' => $request->account_number ?? null,
                        'card_last_four' => $request->payment_method === 'card' ? substr($request->card_number, -4) : null,
                    ],
                ]);

                // In a real application, you would:
                // 1. Process the payment with the payment gateway
                // 2. Update transaction status based on payment result
                // 3. Update user balance if payment successful

                // For demo purposes, we'll simulate successful payment after 2 seconds
                // In production, use webhooks or polling to update status

                // Update user balance (simulating successful payment)
                $user->increment('deposit_balance', $netAmount);
                
                // Update transaction status
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $user->deposit_balance,
                ]);
            });

            return redirect()->route('wallet.index')->with([
                'flash' => [
                    'success' => 'Deposit of KES ' . number_format($amount) . ' processed successfully! KES ' . number_format($fee) . ' fee charged. Net deposit: KES ' . number_format($netAmount)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Deposit failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to process deposit. Please try again.'
                ]
            ]);
        }
    }

    // Withdraw page
    public function withdraw()
    {
        $user = Auth::user();
        
        return Inertia::render('Wallet/Withdraw', [
            'balance' => $user->deposit_balance ?? 0,
            'min_withdrawal' => 500,
            'max_withdrawal' => 50000,
            'withdrawal_fee' => 50, // Flat fee of KES 50
        ]);
    }

    // Process withdrawal
    public function processWithdraw(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:500|max:50000',
            'withdrawal_method' => 'required|in:mpesa,bank',
            'phone_number' => 'required_if:withdrawal_method,mpesa|regex:/^[0-9]{10,12}$/',
            'account_number' => 'required_if:withdrawal_method,bank|string',
            'account_name' => 'required_if:withdrawal_method,bank|string',
            'bank_name' => 'required_if:withdrawal_method,bank|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Calculate total amount (amount + fee)
        $amount = $request->amount;
        $fee = 50; // Flat fee of KES 50
        $totalAmount = $amount + $fee;

        // Check user balance
        if ($user->deposit_balance < $totalAmount) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($totalAmount) . ' (including KES ' . number_format($fee) . ' fee).'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $request, $amount, $fee, $totalAmount) {
                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'WD-' . time() . '-' . strtoupper(uniqid()),
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $amount, // Net amount is what user receives
                    'balance_before' => $user->deposit_balance,
                    'balance_after' => $user->deposit_balance - $totalAmount,
                    'status' => 'pending',
                    'payment_method' => $request->withdrawal_method,
                    'description' => 'Withdrawal via ' . ucfirst($request->withdrawal_method),
                    'metadata' => [
                        'phone_number' => $request->phone_number ?? null,
                        'account_number' => $request->account_number ?? null,
                        'account_name' => $request->account_name ?? null,
                        'bank_name' => $request->bank_name ?? null,
                    ],
                ]);

                // Deduct from user balance
                $user->decrement('deposit_balance', $totalAmount);
                
                // Update transaction status
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $user->deposit_balance,
                ]);

                // In a real application, you would:
                // 1. Process the withdrawal with payment gateway/bank
                // 2. Handle webhook callbacks for status updates
            });

            return redirect()->route('wallet.index')->with([
                'flash' => [
                    'success' => 'Withdrawal of KES ' . number_format($amount) . ' processed successfully! KES ' . number_format($fee) . ' fee charged.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Withdrawal failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to process withdrawal. Please try again.'
                ]
            ]);
        }
    }

    // Transfer page
    public function transfer()
    {
        $user = Auth::user();
        
        return Inertia::render('Wallet/Transfer', [
            'balance' => $user->deposit_balance ?? 0,
            'min_transfer' => 100,
            'max_transfer' => 50000,
            'transfer_fee_percentage' => 0.5, // 0.5% fee
        ]);
    }

    // Process transfer
    public function processTransfer(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100|max:50000',
            'recipient_email' => 'required|email|exists:users,email',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user is transferring to themselves
        if ($request->recipient_email === $user->email) {
            return back()->with([
                'flash' => [
                    'error' => 'You cannot transfer money to yourself.'
                ]
            ]);
        }

        // Get recipient
        $recipient = User::where('email', $request->recipient_email)->first();
        
        if (!$recipient) {
            return back()->with([
                'flash' => [
                    'error' => 'Recipient not found.'
                ]
            ]);
        }

        // Calculate fee (0.5%)
        $amount = $request->amount;
        $fee = $amount * 0.005; // 0.5% fee
        $totalAmount = $amount + $fee;

        // Check user balance
        if ($user->deposit_balance < $totalAmount) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($totalAmount) . ' (including KES ' . number_format($fee) . ' fee).'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $recipient, $request, $amount, $fee, $totalAmount) {
                // Create sender transaction
                $senderTransaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'TRF-SEND-' . time() . '-' . strtoupper(uniqid()),
                    'type' => 'transfer',
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => -$amount, // Negative for sender
                    'balance_before' => $user->deposit_balance,
                    'balance_after' => $user->deposit_balance - $totalAmount,
                    'status' => 'pending',
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'description' => $request->description ?? 'Transfer to ' . $recipient->name,
                    'metadata' => [
                        'recipient_email' => $recipient->email,
                        'is_sender' => true,
                    ],
                ]);

                // Create recipient transaction
                $recipientTransaction = Transaction::create([
                    'user_id' => $recipient->id,
                    'transaction_id' => 'TRF-RECV-' . time() . '-' . strtoupper(uniqid()),
                    'type' => 'transfer',
                    'amount' => $amount,
                    'fee' => 0,
                    'net_amount' => $amount, // Positive for recipient
                    'balance_before' => $recipient->deposit_balance,
                    'balance_after' => $recipient->deposit_balance + $amount,
                    'status' => 'pending',
                    'recipient_id' => $user->id,
                    'recipient_name' => $user->name,
                    'description' => $request->description ?? 'Transfer from ' . $user->name,
                    'metadata' => [
                        'sender_email' => $user->email,
                        'is_recipient' => true,
                    ],
                ]);

                // Deduct from sender
                $user->decrement('deposit_balance', $totalAmount);
                
                // Add to recipient
                $recipient->increment('deposit_balance', $amount);

                // Update transactions status
                $senderTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $user->deposit_balance,
                ]);

                $recipientTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $recipient->deposit_balance,
                ]);
            });

            return redirect()->route('wallet.index')->with([
                'flash' => [
                    'success' => 'Transfer of KES ' . number_format($amount) . ' to ' . $recipient->name . ' completed successfully! KES ' . number_format($fee) . ' fee charged.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Transfer failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to process transfer. Please try again.'
                ]
            ]);
        }
    }

    // Transaction history
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $type = $request->input('type', '');
        $status = $request->input('status', '');
        $search = $request->input('search', '');
        
        $query = Transaction::where('user_id', $user->id);

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'type' => $transaction->type,
                    'type_badge' => $transaction->getTypeBadge(),
                    'amount' => $transaction->getFormattedAmount(),
                    'net_amount' => $transaction->getFormattedNetAmount(),
                    'fee' => $transaction->getFormattedFee(),
                    'status' => $transaction->status,
                    'status_badge' => $transaction->getStatusBadge(),
                    'description' => $transaction->description,
                    'recipient_name' => $transaction->recipient_name,
                    'payment_method' => $transaction->payment_method,
                    'payment_reference' => $transaction->payment_reference,
                    'created_at' => $transaction->created_at->format('M d, Y H:i'),
                    'completed_at' => $transaction->completed_at?->format('M d, Y H:i'),
                    'is_positive' => in_array($transaction->type, ['deposit', 'transfer']) && $transaction->net_amount > 0,
                ];
            });

        // Stats
        $stats = [
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
            'total_deposits' => Transaction::where('user_id', $user->id)->where('type', 'deposit')->where('status', 'completed')->count(),
            'total_withdrawals' => Transaction::where('user_id', $user->id)->where('type', 'withdrawal')->where('status', 'completed')->count(),
            'total_transfers' => Transaction::where('user_id', $user->id)->where('type', 'transfer')->where('status', 'completed')->count(),
        ];

        // Filter options
        $typeOptions = [
            '' => 'All Types',
            'deposit' => 'Deposits',
            'withdrawal' => 'Withdrawals',
            'transfer' => 'Transfers',
            'payment' => 'Payments',
            'loan_application' => 'Loan Applications',
            'loan_repayment' => 'Loan Repayments',
        ];

        $statusOptions = [
            '' => 'All Status',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        return Inertia::render('Wallet/History', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'search' => $search,
            ],
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'balance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Helper: Generate payment reference
    private function generatePaymentReference($method)
    {
        $prefix = strtoupper(substr($method, 0, 3));
        return $prefix . '-' . time() . '-' . strtoupper(uniqid());
    }
}