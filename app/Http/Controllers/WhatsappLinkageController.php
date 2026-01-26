<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLinkage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WhatsappLinkageController extends Controller
{
    // Main WhatsApp linkage page
    public function index()
    {
        $user = Auth::user();
        
        // Get current linkage
        $linkage = WhatsappLinkage::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        $linkageData = null;
        if ($linkage) {
            $linkageData = [
                'id' => $linkage->id,
                'phone_number' => $linkage->phone_number,
                'formatted_phone' => $linkage->getFormattedPhone(),
                'status' => $linkage->status,
                'status_badge' => $linkage->getStatusBadge(),
                'verified_at' => $linkage->verified_at?->format('M d, Y H:i'),
                'whatsapp_link' => $linkage->getWhatsappLink(),
                'whatsapp_chat_link' => $linkage->getWhatsappChatLink(),
                'can_send_verification' => $linkage->canSendVerification(),
                'verification_attempts' => $linkage->verification_attempts,
                'last_attempt' => $linkage->last_verification_attempt?->format('H:i'),
            ];
        }

        // Get verification cooldown
        $cooldown = null;
        if ($linkage && $linkage->verification_sent_at) {
            $minutesSinceLast = now()->diffInMinutes($linkage->verification_sent_at);
            if ($minutesSinceLast < 2) {
                $cooldown = 120 - (now()->diffInSeconds($linkage->verification_sent_at));
            }
        }

        return Inertia::render('WhatsappLinkage/Index', [
            'linkage' => $linkageData,
            'cooldown' => $cooldown,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    // Link WhatsApp number
    public function link(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|regex:/^[0-9]{10,12}$/|unique:whatsapp_linkages,phone_number',
        ], [
            'phone_number.regex' => 'Please enter a valid phone number (10-12 digits)',
            'phone_number.unique' => 'This phone number is already linked to another account',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already has a pending or verified linkage
        $existingLinkage = WhatsappLinkage::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        if ($existingLinkage) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a WhatsApp number linked to your account.'
            ], 400);
        }

        try {
            // Create new linkage
            $linkage = WhatsappLinkage::create([
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'verification_code' => null,
                'status' => 'pending',
                'metadata' => [
                    'linked_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            // Send verification code
            $this->sendVerificationCode($linkage);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp number linked successfully! Verification code sent.',
                'linkage' => [
                    'id' => $linkage->id,
                    'phone_number' => $linkage->phone_number,
                    'formatted_phone' => $linkage->getFormattedPhone(),
                    'status' => $linkage->status,
                    'whatsapp_link' => $linkage->getWhatsappLink(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('WhatsApp linking failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to link WhatsApp number. Please try again.'
            ], 500);
        }
    }

    // Unlink WhatsApp number
    public function unlink(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'confirmation' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $linkage = WhatsappLinkage::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'verified'])
                ->first();

            if (!$linkage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No WhatsApp number found to unlink.'
                ], 404);
            }

            $linkage->update([
                'status' => 'unlinked',
                'metadata' => array_merge($linkage->metadata ?? [], [
                    'unlinked_at' => now(),
                    'reason' => 'user_requested',
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp number unlinked successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('WhatsApp unlinking failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink WhatsApp number. Please try again.'
            ], 500);
        }
    }

    // Verify WhatsApp number
    public function verify(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $linkage = WhatsappLinkage::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        if (!$linkage) {
            return response()->json([
                'success' => false,
                'message' => 'No WhatsApp linkage found.'
            ], 404);
        }

        // Check if verification is blocked
        if ($linkage->verification_attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please try again later.'
            ], 429);
        }

        // Check if code is expired
        if ($linkage->isVerificationCodeExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Increment attempts
        $linkage->increment('verification_attempts');
        $linkage->update(['last_verification_attempt' => now()]);

        // Verify code
        if ($linkage->verification_code === $request->verification_code) {
            $linkage->update([
                'status' => 'verified',
                'verified_at' => now(),
                'metadata' => array_merge($linkage->metadata ?? [], [
                    'verified_at' => now(),
                    'verification_method' => 'manual_code',
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp number verified successfully!',
                'linkage' => [
                    'status' => 'verified',
                    'verified_at' => $linkage->verified_at->format('M d, Y H:i'),
                ]
            ]);
        } else {
            $attemptsLeft = 5 - $linkage->verification_attempts;
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. ' . $attemptsLeft . ' attempts left.',
                'attempts_left' => $attemptsLeft,
            ], 400);
        }
    }

    // Send verification code (helper method)
    private function sendVerificationCode(WhatsappLinkage $linkage)
    {
        try {
            $verificationCode = $linkage->generateVerificationCode();
            
            // Update linkage with new code
            $linkage->update([
                'verification_code' => $verificationCode,
                'verification_sent_at' => now(),
                'verification_attempts' => 0,
                'last_verification_attempt' => null,
                'metadata' => array_merge($linkage->metadata ?? [], [
                    'verification_codes_sent' => ($linkage->metadata['verification_codes_sent'] ?? 0) + 1,
                    'last_code_sent_at' => now(),
                ]),
            ]);

            // In a real application, you would send the code via SMS or WhatsApp API
            // For example, using Twilio, Vonage, or WhatsApp Business API
            
            // Simulate sending for demo purposes
            \Log::info('WhatsApp verification code for ' . $linkage->phone_number . ': ' . $verificationCode);
            
            // For production, you would implement actual SMS sending:
            // $this->sendSMS($linkage->getFormattedPhone(), "Your verification code is: $verificationCode");
            
            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send verification code: ' . $e->getMessage());
            return false;
        }
    }

    // Resend verification code (optional API endpoint)
    public function resendVerification(Request $request)
    {
        $user = Auth::user();
        
        $linkage = WhatsappLinkage::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        if (!$linkage) {
            return response()->json([
                'success' => false,
                'message' => 'No WhatsApp linkage found.'
            ], 404);
        }

        if (!$linkage->canSendVerification()) {
            $minutesSinceLast = $linkage->verification_sent_at ? now()->diffInMinutes($linkage->verification_sent_at) : 0;
            $waitTime = 2 - $minutesSinceLast;
            
            return response()->json([
                'success' => false,
                'message' => 'Please wait ' . $waitTime . ' minute(s) before requesting a new code.',
                'cooldown' => $waitTime * 60, // seconds
            ], 429);
        }

        if ($this->sendVerificationCode($linkage)) {
            return response()->json([
                'success' => true,
                'message' => 'New verification code sent to your WhatsApp number.',
                'cooldown' => 120, // 2 minutes in seconds
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.'
            ], 500);
        }
    }
}