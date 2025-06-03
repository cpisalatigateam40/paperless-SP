<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- SB Admin 2 CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
         body, h1, h2, h3, h4, h5, h6, p, a, span, div {
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
    </style>
</head>
<body class="bg-primary">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg soft-salmon" style="height: 500px;">
                <div class="row no-gutters mt-4">
                    <!-- Image section -->
                    <div class="col-lg-6 d-flex justify-center align-content-center pl-5">
                        <img src="{{ asset('img/login-illustration.png') }}" alt="illustration login" style="max-width: 100%; height: auto; object-fit: contain;"> 
                    </div>

                    <!-- Form section -->
                    <div class="col-lg-6">
                        <div class="p-5">
                            <div class="text-center mb-4">
                                <h1 class="h4 text-gray-900" style="font-weight: 600;">Selamat Datang!</h1>
                                <p class="mb-2">Silakan login untuk melanjutkan</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">{{ $errors->first() }}</div>
                            @endif

                            <form class="user" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="login" class="form-control form-control-user"
                                           placeholder="Username atau Email" required autofocus>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-user"
                                           placeholder="Password" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block" style="margin-top: 3rem;">
                                    Login
                                </button>
                            </form>

                            <hr>
                            {{-- <div class="text-center">
                                <a class="small" href="#">Lupa password?</a>
                            </div> --}}
                        </div>
                    </div>

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
