<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Saudi License Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sls-ink:#0f1b2d; --sls-accent:#2e8b6f; }
        body {
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:linear-gradient(135deg,#0f1b2d 0%,#16263d 100%);
            font-family:'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width:100%; max-width:400px; background:#fff; border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,.35); overflow:hidden;
        }
        .login-head {
            background:var(--sls-ink); color:#fff; padding:1.75rem; text-align:center;
        }
        .login-head .dot {
            display:inline-block; width:12px; height:12px; border-radius:50%;
            background:var(--sls-accent); box-shadow:0 0 0 5px rgba(46,139,111,.25); margin-bottom:.75rem;
        }
        .login-head h1 { font-size:1.15rem; font-weight:700; margin:0; letter-spacing:.3px; }
        .login-head p { margin:.25rem 0 0; font-size:.82rem; color:#8b9bb0; }
        .login-body { padding:1.75rem; }
        .btn-accent { background:var(--sls-accent); border-color:var(--sls-accent); color:#fff; font-weight:600; }
        .btn-accent:hover { background:#26765d; border-color:#26765d; color:#fff; }
        .form-label { font-size:.85rem; font-weight:600; color:var(--sls-ink); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-head">
            <div class="dot"></div>
            <h1>Saudi License Server</h1>
            <p>Admin sign in</p>
        </div>
        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-accent w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
