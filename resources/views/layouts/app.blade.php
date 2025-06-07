<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paperless SP</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
         body, h1, h2, h3, h4, h5, h6, p, a, span, div {
            font-family: 'Poppins', sans-serif !important;
        }

        table {
            font-size: .8rem;
        }

        .container-fluid {
            padding: 1.5rem;
        }

        .card-header {
            padding-inline: 2rem;
            margin-top: .5rem;
        }

        .card-body {
            padding: 2rem;
            /* padding-block: 1rem; */
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

        .table {
            margin-bottom: unset;
        }

        .gap-action {
            gap: .5rem;
        }

    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        @include('partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('partials.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    @yield('script')
</body>
</html>