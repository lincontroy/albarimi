<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Login Detected - Barimax Top</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reuse the same base styles from welcome email with security-focused modifications */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(135deg, #ffffff 0%, #fdfdfe 100%);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.05),
                0 1px 3px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }
        
        .email-header {
            background: linear-gradient(135deg, #4c51bf 0%, #2d3748 100%);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .security-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        
        .notification-title {
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .notification-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .security-alert {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border: 2px solid #fc8181;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .alert-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: #e53e3e;
        }
        
        .alert-title {
            font-weight: 600;
            color: #c53030;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .alert-text {
            color: #742a2a;
            font-size: 15px;
        }
        
        .login-details {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            padding: 12px 0;
        }
        
        .detail-label {
            color: #718096;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 16px;
        }
        
        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #c6f6d5;
            color: #22543d;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin: 15px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4c51bf 0%, #2d3748 100%);
            color: white;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 81, 191, 0.3);
            border: none;
        }
        
        .btn-secondary {
            background: white;
            color: #4c51bf;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .security-tips {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .tips-title {
            font-weight: 600;
            color: #2b6cb0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .tips-list {
            list-style: none;
            padding-left: 0;
        }
        
        .tips-list li {
            padding: 8px 0;
            color: #2c5282;
            position: relative;
            padding-left: 30px;
        }
        
        .tips-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2b6cb0;
            font-weight: bold;
        }
        
        .email-footer {
            padding: 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
        }
        
        @media (max-width: 600px) {
            .email-body {
                padding: 25px 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="security-icon">🔐</div>
            <div class="logo">Barimax Top Security</div>
            <h1 class="notification-title">New Login Detected</h1>
            <p class="notification-subtitle">Your account was accessed from a new device</p>
        </div>
        
        <div class="email-body">
            <div class="security-alert">
                <div class="alert-icon">⚠️</div>
                <div class="alert-title">Security Notification</div>
                <p class="alert-text">
                    A new login to your Barimax Top account was detected. 
                    If this was you, no action is required. If not, please secure your account immediately.
                </p>
            </div>
            
            <h2 style="color: #2d3748; margin-bottom: 20px;">Hello {{ $user->name }},</h2>
            
            <p style="color: #4a5568; margin-bottom: 25px; line-height: 1.7;">
                We detected a new login to your Barimax Top account. 
                Here are the details of this activity:
            </p>
            
            <div class="login-details">
                <div style="font-weight: 600; color: #2d3748; margin-bottom: 20px; font-size: 18px;">
                    📋 Login Activity Details
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Account Name</div>
                        <div class="detail-value">{{ $user->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Login Time</div>
                        <div class="detail-value">{{ $loginTime->format('F d, Y H:i:s') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Device Type</div>
                        <div class="detail-value">{{ $deviceInfo['device'] ?? 'Unknown Device' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">{{ $deviceInfo['location'] ?? 'Location unavailable' }}</div>
                    </div>
                </div>
                
                <div class="verification-badge">
                    <span>✅</span>
                    Login was successful
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="{{ url('/dashboard') }}" class="btn-primary">
                    Go to Dashboard
                </a>
                <a href="{{ url('/account/security') }}" class="btn-secondary">
                    Review Security Settings
                </a>
            </div>
            
            <div class="security-tips">
                <div class="tips-title">🔒 Security Tips</div>
                <ul class="tips-list">
                    <li>Always use a strong, unique password</li>
                    <li>Enable two-factor authentication</li>
                    <li>Never share your login credentials</li>
                    <li>Log out from shared devices</li>
                    <li>Regularly review your account activity</li>
                </ul>
            </div>
            
            <p style="color: #4a5568; margin-top: 30px; font-size: 15px; line-height: 1.7;">
                <strong>Note:</strong> If you didn't initiate this login, please 
                <a href="#" style="color: #4c51bf; text-decoration: none; font-weight: 500;">reset your password immediately</a> 
                and contact our support team.
            </p>
        </div>
        
        <div class="email-footer">
            <p>
                This is an automated security notification from Barimax Top.<br>
                For assistance, contact our support team at support@barimaxtop.com
            </p>
            <p style="margin-top: 15px; color: #a0aec0; font-size: 13px;">
                © {{ date('Y') }} Barimax Top. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>