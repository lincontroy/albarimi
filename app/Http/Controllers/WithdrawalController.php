<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function index()
    {
        // Sample data for demonstration
        $withdrawalData = [
            'withdrawals' => [
                ['id' => 'WD001', 'phone' => '+254712345678', 'amount' => 5000, 'date' => '2024-01-15', 'status' => 'completed'],
                ['id' => 'WD002', 'phone' => '+254723456789', 'amount' => 3000, 'date' => '2024-01-14', 'status' => 'pending'],
                ['id' => 'WD003', 'phone' => '+254734567890', 'amount' => 7000, 'date' => '2024-01-13', 'status' => 'completed'],
            ],
            'availableBalance' => 15000
        ];

        return Inertia::render('WhatsappWithdrawals', $withdrawalData);
    }

    public function store(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        // Process withdrawal request here
        // Check balance, create withdrawal record, etc.

        return back()->with('success', 'Withdrawal request submitted successfully!');
    }
}