<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    @include('partials.theme')
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0d6efd, #212529); padding: 20px; }
        .auth-card { max-width: 420px; width: 100%; }
    </style>
</head>
<body class="auth-page">
    <button type="button" class="btn btn-outline-light border-0 theme-toggle" id="themeToggle" title="Toggle theme" style="position: fixed; top: 16px; right: 16px; z-index: 1050;">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>
    <script>
        (function () {
            var button = document.getElementById('themeToggle');
            if (!button) return;
            button.addEventListener('click', function () {
                var root = document.documentElement;
                var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-bs-theme', next);
                try { localStorage.setItem('bs-theme', next); } catch (e) {}
            });
        })();
    </script>
    <div class="card auth-card border-0 rounded-4 shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="mb-1"><i class="fas fa-dumbbell text-primary"></i> {{ config('app.name') }}</h3>
                <p class="text-muted mb-0">Multi-tenant Gym Management</p>
            </div>

            @include('partials.flash')

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Remember me</label>
                </div>
                <button class="btn btn-primary w-100 py-2">Sign in</button>
            </form>

            <div class="text-center mt-3 text-muted small">
                First time? Ask your platform administrator for an account.
            </div>
        </div>
    </div>
</body>
</html>