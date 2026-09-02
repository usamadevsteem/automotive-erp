<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow-sm border-0" style="width:100%;max-width:400px;border-radius:16px;">
        <div class="card-body p-5 text-center">
            <i class="bi bi-shield-lock text-primary" style="font-size:2.5rem;"></i>
            <h5 class="fw-bold mt-3 mb-1">Two-Factor Authentication</h5>
            <p class="text-muted small mb-4">Enter the 6-digit code from your authenticator app.</p>

            @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('2fa.verify.post') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" name="code"
                           class="form-control form-control-lg text-center fw-bold @error('code') is-invalid @enderror"
                           placeholder="000000" maxlength="6" pattern="\d{6}"
                           inputmode="numeric" autofocus required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>

            <a href="{{ route('logout') }}" class="d-block mt-3 small text-muted"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Use a different account
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
