<?php

// ============================================
// ROUTES (routes/web.php)
// ============================================

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SubmitViewsController;
use App\Http\Controllers\AgentPackageController;
use App\Http\Controllers\WhatsAppWithdrawalController;
use App\Http\Controllers\BonusWithdrawalController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\RewardCenterController;
use App\Http\Controllers\DollarZoneController;
use App\Http\Controllers\VisaCodeController;
use App\Http\Controllers\StarlinkController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\WhatsappLinkageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard'); // Simple redirect
});

Route::middleware(['auth', 'verified'])->group(function () {


     // Cash Flow Routes
     Route::prefix('cash-flow')->name('cash-flow.')->group(function () {
        // Submit Views
        Route::prefix('submit-views')->name('submit-views.')->group(function () {
            Route::get('/', [SubmitViewsController::class, 'index'])->name('index');
            Route::post('/', [SubmitViewsController::class, 'store'])->name('store');
            Route::get('/history', [SubmitViewsController::class, 'history'])->name('history');
            Route::get('/stats', [SubmitViewsController::class, 'stats'])->name('stats');
            Route::get('/{submission}', [SubmitViewsController::class, 'show'])->name('show');
            Route::post('/{submission}/cancel', [SubmitViewsController::class, 'cancel'])->name('cancel');
        });
        
        // WhatsApp Withdrawals (you can add these later)
        Route::prefix('whatsapp-withdrawals')->name('whatsapp-withdrawals.')->group(function () {
            Route::get('/', [WhatsAppWithdrawalController::class, 'index'])->name('index');
            Route::post('/', [WhatsappWithdrawalController::class, 'store'])->name('store');
        });


        
        // Bonus Withdrawals (you can add these later)
        // Route::prefix('bonus-withdrawals')->name('bonus-withdrawals.')->group(function () {
        //     Route::get('/', [BonusWithdrawalController::class, 'index'])->name('index');
        //     Route::post('/', [BonusWithdrawalController::class, 'store'])->name('store');
        // });
    });

    Route::prefix('agent-package')->name('agent-package.')->group(function () {
        Route::get('/', [AgentPackageController::class, 'index'])->name('index');
        Route::post('/purchase', [AgentPackageController::class, 'purchase'])->name('purchase');
        Route::get('/stats', [AgentPackageController::class, 'stats'])->name('stats');
    });
// Team Routes
Route::prefix('team')->name('team.')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('index');
    Route::post('/generate-code', [TeamController::class, 'generateReferralCode'])->name('generate-code');
});
   // routes/web.php
Route::prefix('reward-center')->name('reward-center.')->group(function () {
    Route::get('/welcome-bonus', [RewardCenterController::class, 'index'])->name('index');
    Route::post('/welcome-bonus', [RewardCenterController::class, 'index']); // Add this line
    
    Route::get('/minor-bonus', [RewardCenterController::class, 'purchase'])->name('purchase');
    Route::post('/minor-bonus', [RewardCenterController::class, 'purchase']);
    
    Route::get('/pro-bonus', [RewardCenterController::class, 'stats'])->name('stats');
    Route::post('/pro-bonus', [RewardCenterController::class, 'stats']);
    
    Route::get('/mega-bonus', [RewardCenterController::class, 'stats'])->name('stats');
    Route::post('/mega-bonus', [RewardCenterController::class, 'stats']);
    
    Route::get('/bonus-history', [RewardCenterController::class, 'getBonusHistory'])->name('history');
});
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Add to your Dollar Zone routes group
// Add this to your routes/web.php in the middleware(['auth', 'verified']) group
Route::prefix('certification')->name('certification.')->group(function () {
    Route::get('/', [CertificationController::class, 'index'])->name('index');
    Route::post('/purchase', [CertificationController::class, 'purchase'])->name('purchase');
    Route::get('/history', [CertificationController::class, 'history'])->name('history');
    Route::get('/{id}', [CertificationController::class, 'show'])->name('show');
    Route::post('/{id}/activate', [CertificationController::class, 'activate'])->name('activate');
    Route::post('/check-status', [CertificationController::class, 'checkStatus'])->name('check-status');
});    
    // Visa Codes
Route::prefix('visa-codes')->name('visa-codes.')->group(function () {
    Route::get('/', [VisaCodeController::class, 'index'])->name('index');
    Route::get('/purchase', [VisaCodeController::class, 'purchase'])->name('purchase');
    Route::post('/purchase', [VisaCodeController::class, 'processPurchase'])->name('purchase.process');
    Route::get('/my-codes', [VisaCodeController::class, 'myCodes'])->name('my-codes');
    Route::get('/history', [VisaCodeController::class, 'history'])->name('history');
});
    

    
    // Jobs
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [JobController::class, 'index'])->name('index');
        Route::get('/available', [JobController::class, 'available'])->name('available');
        Route::get('/{job}', [JobController::class, 'show'])->name('show');
        Route::post('/{job}/apply', [JobController::class, 'apply'])->name('apply');
        Route::get('/my-jobs', [JobController::class, 'myJobs'])->name('my-jobs');
        // In your jobs routes group
Route::post('/applications/{application}/withdraw', [JobController::class, 'withdraw'])->name('applications.withdraw');
    });
    
    // Loans
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::get('/apply', [LoanController::class, 'apply'])->name('apply');
        Route::post('/apply', [LoanController::class, 'processApplication'])->name('apply.process');
        Route::get('/calculator', [LoanController::class, 'calculator'])->name('calculator');
        Route::get('/my-loans', [LoanController::class, 'myLoans'])->name('my-loans');
        Route::post('/{loan}/repay', [LoanController::class, 'repay'])->name('repay');
    });
    
    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/deposit', [WalletController::class, 'processDeposit'])->name('deposit.process');
        Route::get('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::post('/withdraw', [WalletController::class, 'processWithdraw'])->name('withdraw.process');
        Route::get('/transfer', [WalletController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [WalletController::class, 'processTransfer'])->name('transfer.process');
        Route::get('/history', [WalletController::class, 'history'])->name('history');
    });
    
    // Endorsement
    Route::prefix('endorsement')->name('endorsement.')->group(function () {
        Route::get('/', [EndorsementController::class, 'index'])->name('index');
        Route::get('/apply', [EndorsementController::class, 'apply'])->name('apply');
        Route::post('/apply', [EndorsementController::class, 'processApplication'])->name('apply.process');
        Route::get('/status', [EndorsementController::class, 'status'])->name('status');
    });
    // Add to your routes in the middleware(['auth', 'verified']) group
Route::prefix('starlink')->name('starlink.')->group(function () {
    Route::get('/', [StarlinkController::class, 'index'])->name('index');
    Route::get('/packages', [StarlinkController::class, 'packages'])->name('packages');
    Route::post('/subscribe', [StarlinkController::class, 'subscribe'])->name('subscribe');
    Route::post('/cancel', [StarlinkController::class, 'cancel'])->name('cancel');
    Route::post('/renew', [StarlinkController::class, 'renew'])->name('renew');
    Route::get('/subscription/{id}', [StarlinkController::class, 'subscription'])->name('subscription');
});
    
    // WhatsApp Linkage
 
Route::prefix('whatsapp-linkage')->name('whatsapp-linkage.')->group(function () {
    Route::get('/', [WhatsappLinkageController::class, 'index'])->name('index');
    Route::post('/link', [WhatsappLinkageController::class, 'link'])->name('link');
    Route::post('/unlink', [WhatsappLinkageController::class, 'unlink'])->name('unlink');
    Route::post('/verify', [WhatsappLinkageController::class, 'verify'])->name('verify');
    Route::post('/resend-verification', [WhatsappLinkageController::class, 'resendVerification'])->name('resend-verification');
});
    
    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::get('/my-reviews', [ReviewController::class, 'myReviews'])->name('my-reviews');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
