<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'interest_rate',
        'term_months',
        'monthly_payment',
        'total_amount',
        'amount_paid',
        'balance',
        'purpose',
        'status',
        'approved_at',
        'disbursed_at',
        'due_date',
        'repayment_schedule',
        'rejection_reason',
        'application_fee',
        'transaction_id',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'application_fee' => 'decimal:2',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'due_date' => 'datetime',
        'repayment_schedule' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDefaulted(): bool
    {
        return $this->status === 'defaulted';
    }

    // Status badge
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['text' => 'Pending Review', 'color' => '#fbbf24', 'bgColor' => 'rgba(251, 191, 36, 0.2)'],
            'approved' => ['text' => 'Approved', 'color' => '#10b981', 'bgColor' => 'rgba(16, 185, 129, 0.2)'],
            'rejected' => ['text' => 'Rejected', 'color' => '#ef4444', 'bgColor' => 'rgba(239, 68, 68, 0.2)'],
            'active' => ['text' => 'Active', 'color' => '#3b82f6', 'bgColor' => 'rgba(59, 130, 246, 0.2)'],
            'completed' => ['text' => 'Completed', 'color' => '#8b5cf6', 'bgColor' => 'rgba(139, 92, 246, 0.2)'],
            'defaulted' => ['text' => 'Defaulted', 'color' => '#dc2626', 'bgColor' => 'rgba(220, 38, 38, 0.2)'],
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }

    // Format currency
    public function getFormattedAmount(): string
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return 'KES ' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalance(): string
    {
        return 'KES ' . number_format($this->balance, 2);
    }

    public function getFormattedMonthlyPayment(): string
    {
        return 'KES ' . number_format($this->monthly_payment, 2);
    }

    // Calculate loan details
    public function calculateLoanDetails(): array
    {
        $principal = (float) $this->amount;
        $interestRate = (float) $this->interest_rate;
        $termMonths = (int) $this->term_months;

        // Calculate monthly interest rate
        $monthlyInterestRate = ($interestRate / 100) / 12;

        // Calculate monthly payment using amortization formula
        if ($monthlyInterestRate > 0) {
            $monthlyPayment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths)) / (pow(1 + $monthlyInterestRate, $termMonths) - 1);
        } else {
            $monthlyPayment = $principal / $termMonths;
        }

        $totalAmount = $monthlyPayment * $termMonths;
        $totalInterest = $totalAmount - $principal;

        // Generate repayment schedule
        $balance = $principal;
        $schedule = [];
        $date = now();

        for ($month = 1; $month <= $termMonths; $month++) {
            $interestPayment = $balance * $monthlyInterestRate;
            $principalPayment = $monthlyPayment - $interestPayment;
            $balance -= $principalPayment;

            if ($balance < 0) {
                $balance = 0;
            }

            $schedule[] = [
                'month' => $month,
                'date' => $date->copy()->addMonths($month)->format('Y-m-d'),
                'payment' => round($monthlyPayment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestPayment, 2),
                'balance' => round($balance, 2),
            ];
        }

        return [
            'monthly_payment' => round($monthlyPayment, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2),
            'repayment_schedule' => $schedule,
        ];
    }

    // Check if user can apply for loan
    public static function userCanApply(User $user): bool
    {
        // Check if user has any active loans
        $hasActiveLoan = self::where('user_id', $user->id)
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        return !$hasActiveLoan;
    }
}