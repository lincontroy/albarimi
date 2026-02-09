<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Barimax Top</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z' fill='%23ffffff'%3E%3C/path%3E%3C/svg%3E");
            background-size: 1200px 60px;
        }
        
        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .welcome-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        
        .email-body {
            padding: 50px 40px 40px;
        }
        
        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 30px;
            letter-spacing: -0.3px;
        }
        
        .message {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 35px;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #f6f8ff 0%, #f0f4ff 100%);
            border-left: 4px solid #667eea;
            padding: 25px;
            border-radius: 16px;
            margin: 30px 0;
        }
        
        .highlight-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .highlight-text {
            color: #4a5568;
            font-size: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 25px 15px;
            background: linear-gradient(135deg, #ffffff 0%, #fdfdfe 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e0;
        }
        
        .stat-icon {
            font-size: 28px;
            margin-bottom: 12px;
            color: #667eea;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            margin: 30px 0;
            border: none;
            cursor: pointer;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .user-details {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid #e2e8f0;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #edf2f7;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #718096;
            font-weight: 500;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 600;
        }
        
        .email-footer {
            padding: 40px 40px 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 25px 0;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: #764ba2;
        }
        
        .footer-text {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 20px;
        }
        
        .copyright {
            color: #a0aec0;
            font-size: 13px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (max-width: 600px) {
            .email-body, .email-footer {
                padding: 30px 20px;
            }
            
            .email-header {
                padding: 30px 20px 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .greeting {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo">
                <div class="logo-icon">✨</div>
                Barimax Top
            </div>
            <h1 class="welcome-title">Welcome Aboard!</h1>
            <p class="welcome-subtitle">Your journey to financial freedom begins here</p>
            <div class="header-wave"></div>
        </div>
        
        <div class="email-body">
            <h2 class="greeting">Hello {{ $user->name }}! 👋</h2>
            
            <p class="message">
                Welcome to Barimax Top! We're thrilled to have you join our community of 
                savvy investors and traders. Your account has been successfully created 
                and is ready to use.
            </p>
            
            <div class="highlight-box">
                <div class="highlight-title">🎉 Congratulations!</div>
                <p class="highlight-text">
                    You've taken the first step towards financial growth. Get ready to 
                    explore our premium investment opportunities and start growing your 
                    wealth with us.
                </p>
            </div>
            
            <div class="user-details">
                <div class="detail-item">
                    <span class="detail-label">Account Name:</span>
                    <span class="detail-value">{{ $user->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email Address:</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Account Type:</span>
                    <span class="detail-value">{{ $user->is_agent ? 'Agent Account' : 'Standard Account' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Join Date:</span>
                    <span class="detail-value">{{ $user->created_at->format('F d, Y') }}</span>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">KES 0</div>
                    <div class="stat-label">Current Balance</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number">0</div>
                    <div class="stat-label">Referrals</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number">New</div>
                    <div class="stat-label">Account Status</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">🚀</div>
                    <div class="stat-number">Ready</div>
                    <div class="stat-label">To Invest</div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ url('/dashboard') }}" class="action-button">
                    Go to Dashboard →
                </a>
            </div>
            
            <div class="highlight-box">
                <div class="highlight-title">📱 Next Steps</div>
                <p class="highlight-text">
                    1. Complete your profile verification<br>
                    2. Make your first deposit<br>
                    3. Explore investment plans<br>
                    4. Start earning with referrals<br>
                    5. Download our mobile app
                </p>
            </div>
            
            <p class="message">
                Need help getting started? Check out our 
                <a href="#" style="color: #667eea; text-decoration: none; font-weight: 500;">Getting Started Guide</a> 
                or contact our support team anytime.
            </p>
        </div>
        
        <div class="email-footer">
            <div class="social-links">
                <a href="#" class="social-icon">📘</a>
                <a href="#" class="social-icon">🐦</a>
                <a href="#" class="social-icon">📷</a>
                <a href="#" class="social-icon">💼</a>
                <a href="#" class="social-icon">📱</a>
            </div>
            
            <p class="footer-text">
                Stay connected with us for updates, tips, and exclusive offers.<br>
                Follow our social media channels to join the conversation.
            </p>
            
            <div class="copyright">
                © {{ date('Y') }} Barimax Top. All rights reserved.<br>
                Financial Services Authority • Nairobi, Kenya<br>
                <small>This email was sent to {{ $user->email }}</small>
            </div>
        </div>
    </div>
</body>
</html>