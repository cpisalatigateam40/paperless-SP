<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Paperless SP</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('img/sausage.png') }}">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery (wajib untuk Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



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

    .border-th {
        border: 1px solid rgb(231, 230, 237) !important;
    }

    ul {
        padding: unset;
    }

    li {
        list-style-type: none;
    }

    thead {
        background-color: #eaecf4 !important;
        color: #6e707e !important;
    }

    th,
    td {
        vertical-align: middle !important;
    }

    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        /* tinggi standar form-control */
        padding: 0.375rem 0.75rem !important;
        /* padding Bootstrap */
        border: 1px solid #ced4da !important;
        /* warna border form-control */
        border-radius: 0.375rem !important;
        /* sudut membulat */
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529 !important;
        line-height: 1.5 !important;
        padding-left: 0 !important;
        /* hapus padding kiri default */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 0.75rem !important;
    }

    /* Saat focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
    </style>

    @yield('style')
</head>

<body>
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.select2-product').select2({
            placeholder: '-- Pilih Produk --',
            allowClear: true,
            width: '100%'
        });
    });
    </script>

    @yield('script')
</body>

</html>