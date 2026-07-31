<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #0c0c14;
            --bg-card: #12121c;
            --accent-gold: #f5a623;
            --accent-gold-dark: #e09600;
            --accent-gold-glow: rgba(245, 166, 35, 0.3);
            --accent-gold-subtle: rgba(245, 166, 35, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --border-color: rgba(245, 166, 35, 0.2);
            --border-color-light: rgba(245, 166, 35, 0.1);
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: var(--font-sans);
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        /* Background Decorations */
        .bg-decoration {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        
        .bg-circle {
            position: absolute;
            border: 1px solid rgba(245, 166, 35, 0.1);
            border-radius: 50%;
        }
        
        .bg-circle:nth-child(1) {
            width: 600px; height: 600px;
            top: -200px; right: -200px;
            animation: float 20s ease-in-out infinite;
        }
        
        .bg-circle:nth-child(2) {
            width: 400px; height: 400px;
            bottom: -100px; left: -100px;
            animation: float 15s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }
        
        /* Login Card */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
        }
        
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 0 0 100px rgba(245, 166, 35, 0.05);
        }
        
        /* Banner */
        .login-banner {
            background: linear-gradient(135deg, 
                rgba(245, 166, 35, 0.1) 0%, 
                rgba(79, 140, 255, 0.1) 50%,
                rgba(74, 222, 128, 0.1) 100%);
            padding: 35px 25px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }
        
        .shield-icon-wrapper {
            position: relative;
            width: 120px;
            height: 140px;
            margin: 0 auto 20px;
        }
        
        .shield-glow {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(245, 166, 35, 0.2) 0%, transparent 70%);
            animation: shieldPulse 3s ease-in-out infinite;
        }
        
        @keyframes shieldPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
        }
        
        .shield-icon {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 120px;
            margin: 0 auto;
            color: var(--accent-gold);
        }
        
        .shield-icon i {
            font-size: 80px;
            filter: drop-shadow(0 10px 30px rgba(245, 166, 35, 0.3));
        }
        
        .login-brand {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--accent-gold);
            letter-spacing: 4px;
            margin-bottom: 8px;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        /* Form */
        .login-form-section {
            padding: 30px 25px;
        }
        
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        
        .shield-input {
            width: 100%;
            padding: 14px 16px;
            padding-left: 45px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .shield-input:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.1);
        }
        
        .shield-input::placeholder {
            color: var(--text-muted);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-gold);
            opacity: 0.5;
        }
        
        .input-icon i {
            font-size: 18px;
        }
        
        .form-check-input {
            background-color: var(--bg-primary);
            border-color: var(--border-color);
        }
        
        .form-check-input:checked {
            background-color: var(--accent-gold);
            border-color: var(--accent-gold);
        }
        
        .form-check-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        /* Submit Button */
        .shield-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-gold-dark) 100%);
            border: none;
            border-radius: var(--radius-md);
            font-family: var(--font-sans);
            font-size: 1rem;
            font-weight: 700;
            color: var(--bg-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .shield-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(245, 166, 35, 0.4);
        }
        
        .shield-submit i {
            font-size: 20px;
        }
        
        /* Alert */
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            color: #ef4444;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        
        /* Footer */
        .login-footer {
            padding: 15px 25px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }
        
        .login-footer a {
            color: var(--accent-gold);
            text-decoration: none;
            font-size: 0.85rem;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-form-section { padding: 20px; }
            .login-banner { padding: 25px 20px; }
        }
    </style>
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-banner">
                <div class="shield-icon-wrapper">
                    <div class="shield-glow"></div>
                    <div class="shield-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
                <div class="login-brand">MRH LICENSE</div>
                <h1 class="login-title">Admin Login</h1>
                <p class="login-subtitle">Sign in to your account</p>
            </div>
            
            <div class="login-form-section">
                @if ($errors->any())
                    <div class="alert-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf
                    <label class="form-label">Email</label>
                    <div class="form-input-wrapper">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="shield-input" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                    </div>
                    
                    <label class="form-label">Password</label>
                    <div class="form-input-wrapper">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="shield-input" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="shield-submit">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <a href="{{ url('/') }}"><i class="bi bi-house me-1"></i> Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
