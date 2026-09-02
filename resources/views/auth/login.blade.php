<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $currentTenant->company_name ?? 'AutoDealer ERP' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); min-height:100vh; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg border-0" style="width:100%;max-width:420px;border-radius:16px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                @if(isset($currentTenant) && $currentTenant->logo_path)
                    <img src="{{ Storage::url($currentTenant->logo_path) }}"
                         alt="Logo" class="img-fluid mb-3" style="max-height:56px;">
                @else
                    <div class="mb-3">
                        <i class="bi bi-car-front-fill text-primary" style="font-size:2.5rem;"></i>
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $currentTenant->company_name ?? 'AutoDealer ERP' }}</h5>
                <p class="text-muted small mb-0">Dealer Management System</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
            @endif

           <form method="POST" action="{{ route('login.post') }}">
    @csrf
    @if(request()->query('tenant'))
        <input type="hidden" name="tenant" value="{{ request()->query('tenant') }}">
    @endif
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="you@example.com" autofocus required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" required>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
