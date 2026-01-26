<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'amount',
        'fee',
        'net_amount',
        'balance_before',
        'balance_after',
        'status',
        'payment_method',
        'payment_reference',
        'recipient_id',
        'recipient_name',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Type helpers
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }

    public function isLoanApplication(): bool
    {
        return $this->type === 'loan_application';
    }

    public function isLoanRepayment(): bool
    {
        return $this->type === 'loan_repayment';
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    // Type badge
    public function getTypeBadge(): array
    {
        $badges = [
            'deposit' => ['text' => 'Deposit', 'color' => '#10b981', 'bgColor' => 'rgba(16, 185, 129, 0.2)', 'icon' => 'arrow-down-circle'],
            'withdrawal' => ['text' => 'Withdrawal', 'color' => '#ef4444', 'bgColor' => 'rgba(239, 68, 68, 0.2)', 'icon' => 'arrow-up-circle'],
            'transfer' => ['text' => 'Transfer', 'color' => '#3b82f6', 'bgColor' => 'rgba(59, 130, 246, 0.2)', 'icon' => 'repeat'],
            'payment' => ['text' => 'Payment', 'color' => '#8b5cf6', 'bgColor' => 'rgba(139, 92, 246, 0.2)', 'icon' => 'credit-card'],
            'loan_application' => ['text' => 'Loan App', 'color' => '#f59e0b', 'bgColor' => 'rgba(245, 158, 11, 0.2)', 'icon' => 'file-text'],
            'loan_repayment' => ['text' => 'Loan Repay', 'color' => '#ec4899', 'bgColor' => 'rgba(236, 72, 153, 0.2)', 'icon' => 'dollar-sign'],
        ];

        return $badges[$this->type] ?? $badges['deposit'];
    }

    // Status badge
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['text' => 'Pending', 'color' => '#fbbf24', 'bgColor' => 'rgba(251, 191, 36, 0.2)'],
            'completed' => ['text' => 'Completed', 'color' => '#10b981', 'bgColor' => 'rgba(16, 185, 129, 0.2)'],
            'failed' => ['text' => 'Failed', 'color' => '#ef4444', 'bgColor' => 'rgba(239, 68, 68, 0.2)'],
            'cancelled' => ['text' => 'Cancelled', 'color' => '#6b7280', 'bgColor' => 'rgba(107, 114, 128, 0.2)'],
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }

    // Format currency
    public function getFormattedAmount(): string
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function getFormattedNetAmount(): string
    {
        return 'KES ' . number_format($this->net_amount, 2);
    }

    public function getFormattedFee(): string
    {
        return 'KES ' . number_format($this->fee, 2);
    }
}