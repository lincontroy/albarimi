<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{


    /**
 * Query M-Pesa transaction status
 */
public function queryMpesaStatus($checkoutRequestID)
{
    try {
        // Get access token
        $accessToken = $this->getMpesaAccessToken(
            env('MPESA_CONSUMER_KEY'),
            env('MPESA_CONSUMER_SECRET')
        );

        if (!$accessToken) {
            \Log::error('Failed to get M-Pesa access token for status query');
            return [
                'success' => false,
                'error' => 'Failed to get access token'
            ];
        }

        // Prepare status query request
        $businessShortCode = env('MPESA_BUSINESS_SHORTCODE');
        $passkey = env('MPESA_PASSKEY');
        $timestamp = date('YmdHis');
        $password = base64_encode($businessShortCode . $passkey . $timestamp);

        $queryData = [
            'BusinessShortCode' => $businessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestID,
        ];

        // Make status query request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($queryData),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseData = json_decode($response, true);

        \Log::info('M-Pesa Status Query Response', [
            'checkout_request_id' => $checkoutRequestID,
            'http_code' => $httpCode,
            'response' => $responseData
        ]);

        if ($httpCode === 200) {
            return [
                'success' => true,
                'data' => $responseData
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['errorMessage'] ?? 'Unknown error',
                'data' => $responseData
            ];
        }

    } catch (\Exception $e) {
        \Log::error('M-Pesa Status Query Exception', [
            'message' => $e->getMessage(),
            'checkout_request_id' => $checkoutRequestID
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Process pending M-Pesa transactions (for cron job)
 */
public function processPendingMpesaTransactions()
{
    \Log::info('Starting M-Pesa pending transactions processing');

    // Find all pending M-Pesa transactions older than 30 seconds
    // This gives time for the initial callback, but still queries if no callback received
    $pendingTransactions = Transaction::where('payment_method', 'mpesa')
        ->where('status', 'pending')
        ->where('created_at', '<=', now()->subSeconds(30))
        ->where('created_at', '>=', now()->subMinutes(30)) // Don't query transactions older than 30 minutes
        ->get();

    $processed = 0;
    $updated = 0;
    $failed = 0;

    foreach ($pendingTransactions as $transaction) {
        $processed++;
        
        \Log::info('Processing pending M-Pesa transaction', [
            'transaction_id' => $transaction->id,
            'checkout_request_id' => $transaction->payment_reference,
            'created_at' => $transaction->created_at
        ]);

        // Extract checkout request ID from payment_reference or metadata
        $checkoutRequestID = $transaction->payment_reference;
        
        if (!$checkoutRequestID && isset($transaction->metadata['checkout_request_id'])) {
            $checkoutRequestID = $transaction->metadata['checkout_request_id'];
        }

        if (!$checkoutRequestID) {
            \Log::warning('No checkout request ID found for transaction', [
                'transaction_id' => $transaction->id
            ]);
            
            // Mark as failed if no checkout ID and older than 10 minutes
            if ($transaction->created_at->diffInMinutes(now()) >= 10) {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'No checkout request ID found',
                        'failed_at' => now()->toDateTimeString()
                    ])
                ]);
                $failed++;
            }
            continue;
        }

        // Query status from M-Pesa
        $result = $this->queryMpesaStatus($checkoutRequestID);

        if ($result['success']) {
            $statusData = $result['data'];
            $resultCode = $statusData['ResultCode'] ?? null;
            $resultDesc = $statusData['ResultDesc'] ?? '';

            // Process based on result code
            if ($resultCode === '0') {
                // Transaction successful
                DB::transaction(function () use ($transaction, $statusData) {
                    $user = User::find($transaction->user_id);
                    
                    if ($user) {
                        // Update user balance
                        $user->increment('deposit_balance', $transaction->net_amount);
                        
                        // Update transaction
                        $transaction->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'balance_after' => $user->deposit_balance,
                            'metadata' => array_merge($transaction->metadata ?? [], [
                                'status_query_result' => $statusData,
                                'mpesa_receipt' => $statusData['CallbackMetadata']['Item'][1]['Value'] ?? null,
                                'transaction_date' => $statusData['CallbackMetadata']['Item'][3]['Value'] ?? null,
                                'phone_number' => $statusData['CallbackMetadata']['Item'][4]['Value'] ?? null,
                                'completed_via_cron' => true
                            ])
                        ]);

                        $updated++;
                        
                        \Log::info('M-Pesa transaction completed via cron', [
                            'transaction_id' => $transaction->id,
                            'receipt' => $statusData['CallbackMetadata']['Item'][1]['Value'] ?? null
                        ]);
                    }
                });
            } elseif (in_array($resultCode, ['1032', '1037', '2001'])) {
                // Transaction cancelled by user or failed
                $failureReasons = [
                    '1032' => 'Transaction cancelled by user',
                    '1037' => 'Transaction timeout - please try again',
                    '2001' => 'Insufficient balance'
                ];

                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failure_reason' => $failureReasons[$resultCode] ?? $resultDesc,
                        'result_code' => $resultCode,
                        'failed_at' => now()->toDateTimeString(),
                        'status_query_result' => $statusData
                    ])
                ]);

                $failed++;
                
                \Log::info('M-Pesa transaction failed via cron', [
                    'transaction_id' => $transaction->id,
                    'result_code' => $resultCode,
                    'reason' => $failureReasons[$resultCode] ?? $resultDesc
                ]);
            } else {
                // Still pending or other status - update metadata but keep pending
                $transaction->update([
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'last_status_check' => now()->toDateTimeString(),
                        'last_status_result' => $statusData
                    ])
                ]);

                \Log::info('M-Pesa transaction still pending', [
                    'transaction_id' => $transaction->id,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc
                ]);
            }
        } else {
            \Log::error('Failed to query M-Pesa status', [
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $checkoutRequestID,
                'error' => $result['error'] ?? 'Unknown error'
            ]);

            // If transaction is older than 30 minutes and still pending, mark as failed
            if ($transaction->created_at->diffInMinutes(now()) >= 30) {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'Transaction timeout - no response from M-Pesa',
                        'failed_at' => now()->toDateTimeString(),
                        'last_status_error' => $result['error'] ?? 'Unknown error'
                    ])
                ]);
                $failed++;
            }
        }

        // Sleep for 1 second between queries to avoid rate limiting
        sleep(1);
    }

    \Log::info('M-Pesa pending transactions processing completed', [
        'processed' => $processed,
        'completed' => $updated,
        'failed' => $failed
    ]);

    return [
        'processed' => $processed,
        'completed' => $updated,
        'failed' => $failed
    ];
}

/**
 * Manual status check endpoint (can be called via browser)
 */
public function checkTransactionStatus(Request $request, $transactionId)
{
    $transaction = Transaction::findOrFail($transactionId);
    
    // Ensure user owns this transaction
    if ($transaction->user_id !== Auth::id()) {
        abort(403);
    }

    if ($transaction->payment_method !== 'mpesa' || $transaction->status !== 'pending') {
        return response()->json([
            'status' => $transaction->status,
            'message' => 'Transaction is not pending or not an M-Pesa transaction'
        ]);
    }

    $checkoutRequestID = $transaction->payment_reference ?? $transaction->metadata['checkout_request_id'] ?? null;
    
    if (!$checkoutRequestID) {
        return response()->json([
            'status' => 'error',
            'message' => 'No checkout request ID found'
        ]);
    }

    $result = $this->queryMpesaStatus($checkoutRequestID);

    if ($result['success']) {
        $statusData = $result['data'];
        $resultCode = $statusData['ResultCode'] ?? null;

        if ($resultCode === '0') {
            // Transaction successful - update it
            DB::transaction(function () use ($transaction, $statusData) {
                $user = User::find($transaction->user_id);
                $user->increment('deposit_balance', $transaction->net_amount);
                
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $user->deposit_balance,
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'status_query_result' => $statusData,
                        'mpesa_receipt' => $statusData['CallbackMetadata']['Item'][1]['Value'] ?? null
                    ])
                ]);
            });

            return response()->json([
                'status' => 'completed',
                'message' => 'Transaction completed successfully',
                'receipt' => $statusData['CallbackMetadata']['Item'][1]['Value'] ?? null
            ]);
        } elseif (in_array($resultCode, ['1032', '1037', '2001'])) {
            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'failure_reason' => $statusData['ResultDesc'] ?? 'Transaction failed',
                    'result_code' => $resultCode
                ])
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => $statusData['ResultDesc'] ?? 'Transaction failed'
            ]);
        } else {
            return response()->json([
                'status' => 'pending',
                'message' => 'Transaction still pending',
                'data' => $statusData
            ]);
        }
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to query status: ' . ($result['error'] ?? 'Unknown error')
        ]);
    }
}


    public function registerurl(){
       
        // access token here
        $consumer_key=env('MPESA_CONSUMER_KEY');
        $consumer_secret=env('MPESA_CONSUMER_SECRET');
        
        $headers=['Content-Type:application/json; charset-utf8'];
          $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
          $curl = curl_init( $url);
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_USERPWD, $consumer_key.':'.$consumer_secret);
         $result=curl_exec($curl);
         $status=curl_getinfo($curl, CURLINFO_HTTP_CODE);
         $result=json_decode($result);
        //  $access_token= $result->access_token;

        $access_token= $this->getMpesaAccessToken($consumer_key, $consumer_secret);


          $url = 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl';  
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer '.$access_token)); //setting custom header  
          $curl_post_data = array(
            //Fill in the request parameters with valid values
            'ShortCode' => env('MPESA_BUSINESS_SHORTCODE'),
            'ResponseType' => 'Completed',
            'ConfirmationURL' => "https://barimaxtop.com/api/response",
            'ValidationURL' => "https://barimaxtop.com//api/response"
          );
          $data_string = json_encode($curl_post_data);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
          $curl_response = curl_exec($curl);
          print_r($curl_response);
          echo $curl_response;
            }
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
            'min_deposit' => 1,
            'max_deposit' => 50000,
            'deposit_fee_percentage' => 0, // 1.5% fee
        ]);
    }


    public function registerMpesaUrls()
    {
        try {
            // Get access token
            $accessToken = $this->getMpesaAccessToken(
                env('MPESA_CONSUMER_KEY'),
                env('MPESA_CONSUMER_SECRET')
            );
    
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get M-Pesa access token'
                ], 500);
            }
    
            // Register validation and confirmation URLs
            $shortCode = env('MPESA_BUSINESS_SHORTCODE');
            $validationUrl = env('MPESA_CALLBACK_URL');
            $confirmationUrl = env('MPESA_CALLBACK_URL');
            
            // For paybill
            $urlRegistrationData = [
                'ShortCode' => $shortCode,
                'ResponseType' => 'Completed', // or 'Cancelled'
                'ConfirmationURL' => $confirmationUrl,
                'ValidationURL' => $validationUrl,
            ];
    
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($urlRegistrationData),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
            ]);
    
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
    
            $responseData = json_decode($response, true);
    
            \Log::info('M-Pesa URL Registration Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);
    
            if ($httpCode === 200 && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                return response()->json([
                    'success' => true,
                    'message' => 'URLs registered successfully',
                    'data' => $responseData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register URLs',
                    'error' => $responseData['errorMessage'] ?? 'Unknown error',
                    'data' => $responseData
                ], 400);
            }
    
        } catch (\Exception $e) {
            \Log::error('M-Pesa URL Registration Exception: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Process deposit
 // Process deposit
public function processDeposit(Request $request)
{
    $user = Auth::user();


    // dd($request->all());

    $request->merge(['account_number' => $user->name]);

    // dd($request->all());
    
    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric|min:1|max:50000',
        'payment_method' => 'required|in:mpesa,bank,card',
        'phone_number' => 'required_if:payment_method,mpesa|regex:/^[0-9]{10,12}$/',
        'account_number' => 'string',
       
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Calculate fee (1.5%)
    $amount = $request->amount;
    $fee = $amount * 0.015; // 1.5% fee
    $netAmount = $amount - $fee;

    $request->payment_method="mpesa";

    // Format phone number for M-Pesa (remove 0 or +254, ensure 254 format)
    if ($request->payment_method === 'mpesa') {
        $phoneNumber = $this->formatMpesaPhoneNumber($request->phone_number);
        
        // Validate that the phone number is now in correct format (254XXXXXXXXX)
        if (!preg_match('/^254[0-9]{9}$/', $phoneNumber)) {
            return back()->with([
                'flash' => [
                    'error' => 'Invalid phone number format. Please use a valid Safaricom number.'
                ]
            ])->withInput();
        }
    }

    try {
        DB::beginTransaction();

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'DEP-' . time() . '-' . strtoupper(uniqid()),
            'type' => 'deposit',
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'balance_before' => $user->deposit_balance,
            'balance_after' => $user->deposit_balance + $netAmount, // Will be updated after confirmation
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_reference' => $this->generatePaymentReference($request->payment_method),
            'description' => 'Deposit via ' . ucfirst($request->payment_method),
            'metadata' => [
                'phone_number' => $request->payment_method === 'mpesa' ? $phoneNumber : null,
                'account_number' => $request->account_number ?? null,
                'card_last_four' => $request->payment_method === 'card' ? substr($request->card_number, -4) : null,
            ],
        ]);

        // Handle different payment methods
        if ($request->payment_method === 'mpesa') {
            // Initiate M-Pesa STK Push
            $mpesaResponse = $this->initiateMpesaStkPush($transaction, $phoneNumber, $amount);
            
            if ($mpesaResponse['success']) {
                // Update transaction with M-Pesa details
                $transaction->update([
                    'payment_reference' => $mpesaResponse['CheckoutRequestID'],
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'mpesa_response' => $mpesaResponse,
                        'merchant_request_id' => $mpesaResponse['MerchantRequestID'],
                        'checkout_request_id' => $mpesaResponse['CheckoutRequestID'],
                    ]),
                ]);

                DB::commit();

                // Return response with M-Pesa prompt info
                return redirect()->route('wallet.deposit.status', ['transaction' => $transaction->id])->with([
                    'flash' => [
                        'info' => 'Please check your phone and enter your M-Pesa PIN to complete the deposit. You will be redirected once payment is confirmed.',
                        'transaction_id' => $transaction->id,
                        'checkout_request_id' => $mpesaResponse['CheckoutRequestID'],
                    ]
                ]);
            } else {
                DB::rollBack();
                \Log::error('M-Pesa STK Push failed', ['response' => $mpesaResponse]);
                
                return back()->with([
                    'flash' => [
                        'error' => 'Failed to initiate M-Pesa payment. Please try again. Error: ' . ($mpesaResponse['error'] ?? 'Unknown error')
                    ]
                ])->withInput();
            }
        } else {
            // For bank/card - simulate success (as before)
            $user->increment('deposit_balance', $netAmount);
            
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'balance_after' => $user->deposit_balance,
            ]);

            DB::commit();

            return redirect()->route('wallet.index')->with([
                'flash' => [
                    'success' => 'Deposit of KES ' . number_format($amount) . ' processed successfully! KES ' . number_format($fee) . ' fee charged. Net deposit: KES ' . number_format($netAmount)
                ]
            ]);
        }

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Deposit failed: ' . $e->getMessage());
        return back()->with([
            'flash' => [
                'error' => 'Failed to process deposit. Please try again.'
            ]
        ]);
    }
}

/**
 * Format phone number to M-Pesa format (254XXXXXXXXX)
 */
private function formatMpesaPhoneNumber($phone)
{
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it starts with 0 (Kenyan format)
    if (substr($phone, 0, 1) === '0') {
        $phone = '254' . substr($phone, 1);
    }
    // Check if it starts with 254 (already in correct format)
    elseif (substr($phone, 0, 3) === '254') {
        // Already correct format
    }
    // Check if it starts with +254
    elseif (substr($phone, 0, 4) === '254') {
        // Already correct format after removing +
    }
    else {
        // Assume it's a Safaricom number without country code
        $phone = '254' . $phone;
    }
    
    return $phone;
}



/**
 * Initiate M-Pesa STK Push
 */
private function initiateMpesaStkPush($transaction, $phoneNumber, $amount)
{
    try {
        // M-Pesa API credentials from .env
        $consumerKey = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $businessShortCode = env('MPESA_BUSINESS_SHORTCODE');
        $passkey = env('MPESA_PASSKEY');
        $callbackUrl = env('MPESA_CALLBACK_URL', route('mpesa.callback'));
        
        // Get access token
        $accessToken = $this->getMpesaAccessToken($consumerKey, $consumerSecret);
        
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to get M-Pesa access token'
            ];
        }

        // Generate timestamp
        $timestamp = date('YmdHis');
        
        // Generate password
        $password = base64_encode($businessShortCode . $passkey . $timestamp);

        // STK Push request data
        $stkPushData = [
            'BusinessShortCode' => $businessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => round($amount), // M-Pesa accepts whole numbers
            'PartyA' => $phoneNumber,
            'PartyB' => $businessShortCode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => 'DEP' . $transaction->id,
            'TransactionDesc' => 'Wallet Deposit',
        ];

        // Make STK Push request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($stkPushData),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseData = json_decode($response, true);

        // Check if request was successful
        if ($httpCode === 200 && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
            return [
                'success' => true,
                'MerchantRequestID' => $responseData['MerchantRequestID'],
                'CheckoutRequestID' => $responseData['CheckoutRequestID'],
                'ResponseCode' => $responseData['ResponseCode'],
                'ResponseDescription' => $responseData['ResponseDescription'],
                'CustomerMessage' => $responseData['CustomerMessage'],
            ];
        } else {
            \Log::error('M-Pesa STK Push failed', [
                'http_code' => $httpCode,
                'response' => $responseData,
                'phone' => $phoneNumber,
                'amount' => $amount
            ]);

            return [
                'success' => false,
                'error' => $responseData['errorMessage'] ?? 'M-Pesa request failed',
                'response' => $responseData
            ];
        }

    } catch (\Exception $e) {
        \Log::error('M-Pesa STK Push exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get M-Pesa access token
 */
private function getMpesaAccessToken($consumerKey, $consumerSecret)
{
    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }

        \Log::error('Failed to get M-Pesa access token', [
            'http_code' => $httpCode,
            'response' => $response
        ]);

        return null;

    } catch (\Exception $e) {
        \Log::error('M-Pesa access token exception: ' . $e->getMessage());
        return null;
    }
}

/**
 * M-Pesa callback handler
 */
public function mpesaCallback(Request $request)
{
    \Log::info('M-Pesa Callback received', $request->all());

    try {
        $callbackData = $request->all();

        // Check if it's the STK Push callback
        if (isset($callbackData['Body']['stkCallback'])) {
            $stkCallback = $callbackData['Body']['stkCallback'];
            $checkoutRequestID = $stkCallback['CheckoutRequestID'];
            $resultCode = $stkCallback['ResultCode'];
            $resultDesc = $stkCallback['ResultDesc'];

            // Find transaction by checkout request ID in metadata
            $transaction = Transaction::where('metadata->checkout_request_id', $checkoutRequestID)
                ->orWhere('payment_reference', $checkoutRequestID)
                ->first();

            if (!$transaction) {
                \Log::error('Transaction not found for CheckoutRequestID: ' . $checkoutRequestID);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Transaction not found']);
            }

            $user = User::find($transaction->user_id);

            if (!$user) {
                \Log::error('User not found for transaction: ' . $transaction->id);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'User not found']);
            }

            // Process based on result code
            if ($resultCode == 0) {
                // Success - transaction completed
                DB::transaction(function () use ($transaction, $user, $stkCallback) {
                    // Extract callback metadata
                    $callbackMetadata = [];
                    if (isset($stkCallback['CallbackMetadata']['Item'])) {
                        foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                            $callbackMetadata[$item['Name']] = $item['Value'] ?? null;
                        }
                    }

                    // Update user balance
                    $user->increment('deposit_balance', $transaction->net_amount);

                    // Update transaction
                    $transaction->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'balance_after' => $user->deposit_balance,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'mpesa_callback' => $callbackMetadata,
                            'mpesa_receipt' => $callbackMetadata['MpesaReceiptNumber'] ?? null,
                            'transaction_date' => $callbackMetadata['TransactionDate'] ?? null,
                            'phone_number' => $callbackMetadata['PhoneNumber'] ?? null,
                        ]),
                    ]);
                });

                \Log::info('M-Pesa transaction completed', [
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $checkoutRequestID,
                    'receipt' => $callbackMetadata['MpesaReceiptNumber'] ?? null
                ]);

            } else {
                // Failed or cancelled
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'mpesa_result_code' => $resultCode,
                        'mpesa_result_desc' => $resultDesc,
                    ]),
                ]);

                \Log::info('M-Pesa transaction failed', [
                    'transaction_id' => $transaction->id,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc
                ]);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }

        return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);

    } catch (\Exception $e) {
        \Log::error('M-Pesa callback error: ' . $e->getMessage());
        return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Server error']);
    }
}

/**
 * Check deposit status (for polling)
 */
public function depositStatus($transactionId)
{
    $transaction = Transaction::findOrFail($transactionId);
    
    // Ensure user owns this transaction
    if ($transaction->user_id !== Auth::id()) {
        abort(403);
    }

    return response()->json([
        'status' => $transaction->status,
        'transaction_id' => $transaction->id,
        'checkout_request_id' => $transaction->metadata['checkout_request_id'] ?? null,
        'completed_at' => $transaction->completed_at,
        'message' => $transaction->status === 'completed' 
            ? 'Payment completed successfully!' 
            : ($transaction->status === 'failed' 
                ? 'Payment failed. Please try again.' 
                : 'Waiting for payment confirmation...'),
    ]);
}

/**
 * Deposit status page (for redirect after STK push)
 */
public function depositStatusPage($transactionId)
{
    $transaction = Transaction::findOrFail($transactionId);
    
    // Ensure user owns this transaction
    if ($transaction->user_id !== Auth::id()) {
        abort(403);
    }

    return Inertia::render('Wallet/DepositStatus', [
        'transaction' => [
            'id' => $transaction->id,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'fee' => $transaction->fee,
            'net_amount' => $transaction->net_amount,
            'payment_method' => $transaction->payment_method,
            'checkout_request_id' => $transaction->metadata['checkout_request_id'] ?? null,
            'created_at' => $transaction->created_at->format('M d, Y H:i:s'),
        ],
        'balance' => Auth::user()->deposit_balance,
    ]);
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