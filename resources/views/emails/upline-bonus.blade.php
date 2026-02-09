<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Bonus Earned - Barimax Top</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        
        .bonus-card {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin: 25px 0;
        }
        
        .bonus-amount {
            font-size: 36px;
            font-weight: 700;
            color: #059669;
            margin: 10px 0;
        }
        
        .bonus-label {
            color: #065f46;
            font-weight: 600;
            font-size: 16px;
        }
        
        .info-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #64748b;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
        }
        
        .cta-button {
            display: block;
            background: #10b981;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin: 25px 0;
            transition: background 0.3s;
        }
        
        .cta-button:hover {
            background: #059669;
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
            
            .bonus-amount {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="header-icon">💰</div>
            <h1 class="header-title">Referral Bonus Earned!</h1>
        </div>
        
        <div class="email-body">
            <h2 class="greeting">Great work, {{ $upline->name }}!</h2>
            
            <div class="bonus-card">
                <div class="bonus-label">YOUR COMMISSION</div>
                <div class="bonus-amount">KES {{ number_format($commissionAmount) }}</div>
                <div style="color: #047857; margin-top: 10px;">
                    80% commission from downline's purchase
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">From:</span>
                    <span class="info-value">{{ $downline->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Package:</span>
                    <span class="info-value">{{ $packageName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">{{ now()->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">New Balance:</span>
                    <span class="info-value">KES {{ number_format($upline->deposit_balance) }}</span>
                </div>
            </div>
            
            <a href="{{ url('/dashboard') }}" class="cta-button">
                View Dashboard & Manage Earnings
            </a>
            
            <div style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px;">
                <p>Keep sharing your referral link to earn more!</p>
                <div style="background: #f3f4f6; padding: 10px; border-radius: 6px; margin-top: 10px; font-family: monospace; font-size: 13px;">
                    {{ url('/register?ref=' . $upline->id) }}
                </div>
            </div>
        </div>
        
        <div class="email-footer">
            <p>© {{ date('Y') }} Barimax Top • Affiliate Program</p>
            <p style="margin-top: 10px; font-size: 13px;">This is an automated notification</p>
        </div>
    </div>
</body>
</html>