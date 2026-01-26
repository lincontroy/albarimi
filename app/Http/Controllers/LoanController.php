<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LoanController extends Controller
{

    // Show repay page
public function repayPage(Loan $loan)
{
    $user = Auth::user();
    
    if ($loan->user_id !== $user->id) {
        abort(403);
    }

    if (!$loan->isActive()) {
        return redirect()->route('loans.my-loans')->with([
            'flash' => [
                'error' => 'This loan is not active for repayment.'
            ]
        ]);
    }

    $loanData = [
        'id' => $loan->id,
        'amount' => $loan->getFormattedAmount(),
        'balance' => $loan->getFormattedBalance(),
        'monthly_payment' => $loan->getFormattedMonthlyPayment(),
        'due_date' => $loan->due_date?->format('M d, Y'),
    ];

    return Inertia::render('Loans/Repay', [
        'loan' => $loanData,
        'userBalance' => $user->deposit_balance ?? 0,
    ]);
}
    // Main loans page
    public function index()
    {
        $user = Auth::user();
        
        // Get active loans for display
        $activeLoans = Loan::where('user_id', $user->id)
            ->whereIn('status', ['active', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'amount' => $loan->getFormattedAmount(),
                    'total_amount' => $loan->getFormattedTotalAmount(),
                    'balance' => $loan->getFormattedBalance(),
                    'monthly_payment' => $loan->getFormattedMonthlyPayment(),
                    'term_months' => $loan->term_months,
                    'interest_rate' => $loan->interest_rate,
                    'purpose' => $loan->purpose,
                    'status' => $loan->status,
                    'status_badge' => $loan->getStatusBadge(),
                    'due_date' => $loan->due_date?->format('M d, Y'),
                    'days_until_due' => $loan->due_date ? now()->diffInDays($loan->due_date, false) : null,
                ];
            });

        // Loan eligibility
        $canApply = Loan::userCanApply($user);
        $hasPendingApplication = Loan::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        // Stats
        $stats = [
            'total_loans' => Loan::where('user_id', $user->id)->count(),
            'active_loans' => Loan::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_borrowed' => Loan::where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->sum('amount'),
            'total_repaid' => Loan::where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->sum('amount_paid'),
        ];

        return Inertia::render('Loans/Index', [
            'activeLoans' => $activeLoans,
            'canApply' => $canApply,
            'hasPendingApplication' => $hasPendingApplication,
            'stats' => $stats,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Apply for loan page
    public function apply()
    {
        $user = Auth::user();
        
        // Check if user can apply
        if (!Loan::userCanApply($user)) {
            return redirect()->route('loans.index')->with([
                'flash' => [
                    'error' => 'You already have an active loan. Please repay your existing loan before applying for a new one.'
                ]
            ]);
        }

        // Check if user has pending application
        $hasPendingApplication = Loan::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingApplication) {
            return redirect()->route('loans.index')->with([
                'flash' => [
                    'error' => 'You already have a pending loan application. Please wait for it to be reviewed.'
                ]
            ]);
        }

        // Check user balance for application fee
        $applicationFee = 200;
        $hasEnoughBalance = $user->deposit_balance >= $applicationFee;

        return Inertia::render('Loans/Apply', [
            'applicationFee' => $applicationFee,
            'hasEnoughBalance' => $hasEnoughBalance,
            'userBalance' => $user->deposit_balance ?? 0,
            'minAmount' => 1000,
            'maxAmount' => 50000,
            'defaultTerm' => 12,
            'interestRate' => 5.0,
        ]);
    }

    // Process loan application
    public function processApplication(Request $request)
    {
        $user = Auth::user();
        $applicationFee = 200;

        // Validate
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000|max:50000',
            'term_months' => 'required|integer|min:3|max:36',
            'purpose' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user can apply
        if (!Loan::userCanApply($user)) {
            return back()->with([
                'flash' => [
                    'error' => 'You already have an active loan. Please repay your existing loan before applying for a new one.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $applicationFee) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($applicationFee) . ' for the application fee.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $request, $applicationFee) {
                // Deduct application fee
                $user->decrement('deposit_balance', $applicationFee);

                // Calculate loan details
                $loan = new Loan([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'term_months' => $request->term_months,
                    'interest_rate' => 5.0, // 5% interest rate
                    'purpose' => $request->purpose,
                    'application_fee' => $applicationFee,
                    'status' => 'pending',
                    'transaction_id' => 'LOAN-' . time() . '-' . strtoupper(uniqid()),
                ]);

                // Calculate repayment details
                $details = $loan->calculateLoanDetails();
                $loan->monthly_payment = $details['monthly_payment'];
                $loan->total_amount = $details['total_amount'];
                $loan->balance = $details['total_amount'];
                $loan->repayment_schedule = $details['repayment_schedule'];
                $loan->due_date = now()->addMonths($request->term_months);

                $loan->save();

                // Create transaction record (optional)
                // You can add to your existing transaction system here
            });

            return redirect()->route('loans.my-loans')->with([
                'flash' => [
                    'success' => 'Loan application submitted successfully! KES ' . number_format($applicationFee) . ' application fee deducted. Your application is under review.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Loan application failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to submit loan application. Please try again.'
                ]
            ]);
        }
    }

    // Loan calculator
    public function calculator(Request $request)
    {
        $amount = $request->input('amount', 10000);
        $term = $request->input('term', 12);
        $interestRate = 5.0;

        // Create temporary loan to calculate
        $loan = new Loan([
            'amount' => $amount,
            'term_months' => $term,
            'interest_rate' => $interestRate,
        ]);

        $details = $loan->calculateLoanDetails();

        return Inertia::render('Loans/Calculator', [
            'calculation' => [
                'amount' => (float) $amount,
                'term' => (int) $term,
                'interest_rate' => $interestRate,
                'monthly_payment' => $details['monthly_payment'],
                'total_amount' => $details['total_amount'],
                'total_interest' => $details['total_interest'],
                'repayment_schedule' => array_slice($details['repayment_schedule'], 0, 6), // Show first 6 months
            ],
            'userBalance' => Auth::user()->deposit_balance ?? 0,
        ]);
    }

    // User's loans
    public function myLoans()
    {
        $user = Auth::user();
        
        $loans = Loan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($loan) {
                return [
                    'id' => $loan->id,
                    'amount' => $loan->getFormattedAmount(),
                    'total_amount' => $loan->getFormattedTotalAmount(),
                    'balance' => $loan->getFormattedBalance(),
                    'amount_paid' => 'KES ' . number_format($loan->amount_paid, 2),
                    'monthly_payment' => $loan->getFormattedMonthlyPayment(),
                    'term_months' => $loan->term_months,
                    'interest_rate' => $loan->interest_rate,
                    'purpose' => $loan->purpose,
                    'status' => $loan->status,
                    'status_badge' => $loan->getStatusBadge(),
                    'application_fee' => 'KES ' . number_format($loan->application_fee, 2),
                    'due_date' => $loan->due_date?->format('M d, Y'),
                    'days_until_due' => $loan->due_date ? now()->diffInDays($loan->due_date, false) : null,
                    'can_repay' => $loan->isActive() && $loan->balance > 0,
                    'created_at' => $loan->created_at->format('M d, Y'),
                    'approved_at' => $loan->approved_at?->format('M d, Y'),
                    'disbursed_at' => $loan->disbursed_at?->format('M d, Y'),
                ];
            });

        // Stats
        $stats = [
            'total' => Loan::where('user_id', $user->id)->count(),
            'pending' => Loan::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => Loan::where('user_id', $user->id)->where('status', 'approved')->count(),
            'active' => Loan::where('user_id', $user->id)->where('status', 'active')->count(),
            'completed' => Loan::where('user_id', $user->id)->where('status', 'completed')->count(),
            'rejected' => Loan::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'total_borrowed' => Loan::where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->sum('amount'),
            'total_repaid' => Loan::where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->sum('amount_paid'),
            'total_interest' => Loan::where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->sum(DB::raw('total_amount - amount')),
        ];

        return Inertia::render('Loans/MyLoans', [
            'loans' => $loans,
            'stats' => $stats,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Repay loan
    public function repay(Request $request, Loan $loan)
    {
        $user = Auth::user();
        
        // Check if loan belongs to user
        if ($loan->user_id !== $user->id) {
            abort(403);
        }

        // Check if loan is active
        if (!$loan->isActive()) {
            return back()->with([
                'flash' => [
                    'error' => 'This loan is not active for repayment.'
                ]
            ]);
        }

        // Check if loan is already paid
        if ($loan->balance <= 0) {
            return back()->with([
                'flash' => [
                    'error' => 'This loan is already fully paid.'
                ]
            ]);
        }

        // Get repayment amount
        $repaymentAmount = $request->input('amount', $loan->monthly_payment);
        
        // Validate repayment amount
        if ($repaymentAmount <= 0) {
            return back()->with([
                'flash' => [
                    'error' => 'Invalid repayment amount.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $repaymentAmount) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($repaymentAmount) . ' to make this payment.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $loan, $repaymentAmount) {
                // Deduct payment from user balance
                $user->decrement('deposit_balance', $repaymentAmount);

                // Update loan
                $loan->amount_paid += $repaymentAmount;
                $loan->balance -= $repaymentAmount;
                
                // Check if loan is fully paid
                if ($loan->balance <= 0) {
                    $loan->balance = 0;
                    $loan->status = 'completed';
                }
                
                $loan->save();

                // Create transaction record (optional)
                // You can add to your existing transaction system here
            });

            return back()->with([
                'flash' => [
                    'success' => 'Payment of KES ' . number_format($repaymentAmount) . ' processed successfully.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Loan repayment failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to process payment. Please try again.'
                ]
            ]);
        }

        
    }
}