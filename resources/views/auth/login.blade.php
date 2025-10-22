<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
    body,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p,
    a,
    span,
    div {
        font-family: 'Poppins', sans-serif !important;
    }

    .bg-primary {
        background: #cc7064 !important;
    }

    .soft-salmon {
        background-color: #FFF4F2;
    }

    #content {
        background-color: #FFF4F2;
    }

    .btn-primary {
        background-color: #5b96c9;
        border: #5b96c9;
    }

    .bg-image {
        background-image: url('{{ asset('img/hero.jpg') }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: fixed;
        min-height: 100vh;
        position: relative;
        /* penting agar overlay bisa menempel di dalam */
        z-index: 0;
    }

    .bg-image::before {
        content: "";
        position: absolute;
        inset: 0;
        /* set top, right, bottom, left = 0 */
        background-color: rgba(204, 112, 100, 0.8);
        /* #cc7064 dengan opacity 80% */
        z-index: 0;
    }

    /* Pastikan konten di atas overlay tetap terlihat */
    .bg-image>* {
        position: relative;
        z-index: 1;
    }

    /* Kartu login bergaya kaca */
    .login-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .login-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #5b96c9;
        border-color: #5b96c9;
        border-radius: 10px;
        transition: 0.2s;
    }

    .btn-primary:hover {
        background-color: #477daa;
        border-color: #477daa;
        transform: scale(1.02);
    }

    .form-control-user {
        border-radius: 10px;
        padding: 10px 15px;
        font-size: 0.95rem;
    }

    .form-control-user:focus {
        border-color: #5b96c9;
        box-shadow: 0 0 0 0.15rem rgba(91, 150, 201, 0.25);
    }

    label.form-label {
        font-weight: 500;
    }
    </style>
</head>

<body class="bg-image">

    <div class="container d-flex justify-content-center align-items-center bg-image" style="min-height: 100vh;">
        <div class="col-xl-6 col-lg-7 col-md-9">
            <div class="card login-card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 text-dark fw-bold mb-2" style="font-weight: bold; margin-top: -1rem;">Selamat
                            Datang 👋</h1>
                        <p class="text-muted">Masuk untuk melanjutkan ke sistem</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger shadow-sm rounded-lg py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
                    </div>
                    @endif

                    <form class="user" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label small text-muted mb-1">Username atau Email</label>
                            <input type="text" name="login" class="form-control form-control-user"
                                placeholder="Masukkan username atau email..." required autofocus>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label small text-muted mb-1">Password</label>
                            <input type="password" name="password" class="form-control form-control-user"
                                placeholder="Masukkan password..." required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block py-3 fw-semibold">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
</body>

</html>