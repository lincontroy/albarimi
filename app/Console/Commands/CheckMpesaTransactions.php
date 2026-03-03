<?php

namespace App\Console\Commands;

use App\Http\Controllers\WalletController;
use Illuminate\Console\Command;

class CheckMpesaTransactions extends Command
{
    protected $signature = 'mpesa:check-transactions';
    protected $description = 'Check status of pending M-Pesa transactions';

    public function handle(WalletController $walletController)
    {
        $this->info('Starting M-Pesa transaction status check...');
        
        $result = $walletController->processPendingMpesaTransactions();
        
        $this->info("Processed: {$result['processed']} transactions");
        $this->info("Completed: {$result['completed']} transactions");
        $this->info("Failed: {$result['failed']} transactions");
        
        return Command::SUCCESS;
    }
}