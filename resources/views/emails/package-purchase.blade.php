<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Activated - Barimax Top</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f9ff;
            padding: 20px;
            color: #1f2937;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        
        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .header-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .package-card {
            background: white;
            border: 2px solid #93c5fd;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin: 25px 0;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
        }
        
        .package-name {
            font-size: 24px;
            font-weight: 600;
            color: #1e40af;
            margin: 10px 0;
        }
        
        .package-amount {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
            margin: 15px 0;
        }
        
        .features-list {
            list-style: none;
            margin: 20px 0;
            text-align: left;
        }
        
        .features-list li {
            padding: 8px 0;
            color: #4b5563;
            padding-left: 25px;
            position: relative;
        }
        
        .features-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        .duration-info {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .duration-item {
            text-align: center;
        }
        
        .duration-label {
            color: #64748b;
            font-size: 14px;
        }
        
        .duration-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 16px;
            margin-top: 5px;
        }
        
        .affiliate-section {
            background: #f0fdf4;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .affiliate-title {
            color: #065f46;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
        }
        
        .cta-button {
            flex: 1;
            background: #3b82f6;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .cta-button.secondary {
            background: white;
            color: #3b82f6;
            border: 2px solid #3b82f6;
        }
        
        .cta-button:hover {
            background: #2563eb;
        }
        
        .cta-button.secondary:hover {
            background: #3b82f6;
            color: white;
        }
        
        .email-footer {
            padding: 25px;
            text-align: center;
            background: #f1f5f9;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (max-width: 600px) {
            .email-body, .email-header {
                padding: 25px 20px;
            }
            
            .package-amount {
                font-size: 28px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="header-icon">📦</div>
            <h1 class="header-title">Package Activated Successfully!</h1>
        </div>
        
        <div class="email-body">
            <h2 class="greeting">Welcome, {{ $user->name }}!</h2>
            
            <div class="package-card">
                <h3 class="package-name">{{ $packageDetails['name'] }}</h3>
                <div class="package-amount">KES {{ number_format($packageDetails['amount']) }}</div>
                
                <ul class="features-list">
                    @foreach($packageDetails['features'] as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="duration-info">
                <div class="duration-item">
                    <div class="duration-label">Activated</div>
                    <div class="duration-value">{{ now()->format('M d') }}</div>
                </div>
                <div class="duration-item">
                    <div class="duration-label">Duration</div>
                    <div class="duration-value">{{ $packageDetails['duration_days'] }} days</div>
                </div>
                <div class="duration-item">
                    <div class="duration-label">Expires</div>
                    <div class="duration-value">{{ now()->addDays($packageDetails['duration_days'])->format('M d') }}</div>
                </div>
            </div>
            
            @if($upline)
            <div class="affiliate-section">
                <div class="affiliate-title">Affiliate Bonus Generated! 🎉</div>
                <p style="color: #047857; margin: 10px 0;">
                    Your purchase earned <strong>{{ $upline->name }}</strong> a referral bonus of 
                    <strong>KES {{ number_format($commissionAmount) }}</strong>
                </p>
            </div>
            @endif
            
            <div class="cta-buttons">
                <a href="{{ url('/dashboard') }}" class="cta-button">
                    Go to Dashboard
                </a>
                <a href="{{ url('/referrals') }}" class="cta-button secondary">
                    Start Earning Referrals
                </a>
            </div>
            
            <div style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px;">
                <p>Share your referral link to earn commissions when others join:</p>
                <div style="background: #f3f4f6; padding: 10px; border-radius: 6px; margin-top: 10px; font-family: monospace; font-size: 13px;">
                    {{ url('/register?ref=' . $user->id) }}
                </div>
            </div>
        </div>
        
        <div class="email-footer">
            <p>© {{ date('Y') }} Barimax Top • Affiliate Program</p>
            <p style="margin-top: 10px; font-size: 13px;">Need help? Contact support@barimaxtop.com</p>
        </div>
    </div>
</body>
</html>