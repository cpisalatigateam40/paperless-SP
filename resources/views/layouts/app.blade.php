<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paperless SP</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('img/sausage.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->

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
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    #content-wrapper {
        width: 100%;
        min-width: 0; /* penting agar tidak overflow di mobile */
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

    /* Warna tombol sidebar mobile */
    #sidebarToggleTop {
        color: #CC7064 !important;
        border: none;
    }

    /* Warna icon (fa-bars) */
    #sidebarToggleTop i {
        color: #CC7064 !important;
    }

    @media (max-width: 768px) {

        .card-header.d-flex.justify-content-between {
            flex-direction: column;
            align-items: stretch;
            gap: .5rem;
        }

        .card-header .d-flex {
            flex-wrap: wrap;
            gap: .5rem;
        }

        .card-header form {
            width: 100%;
        }

        .card-header input[type="file"] {
            width: 100%;
        }

        .card-header .btn {
            width: 100%;
        }

        .form-select, .form-control, .select2-selection {
            margin-bottom: .5rem;
        }

        .soft-salmon {
            background: #fff;
        }
    }

    /* Default = desktop → tetap inline */
    .d-flex.align-items-center.flex-wrap {
        gap: .4rem;
    }

    /* Mobile layout */
    @media (max-width: 768px) {

        /* wadah utama → jadi kolom */
        .d-flex.align-items-center.flex-wrap {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
        }

        /* form di dalamnya → ikut kolom */
        .d-flex.align-items-center.flex-wrap form.d-flex {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            gap: .4rem;
        }

        /* input file melebar */
        .d-flex.align-items-center.flex-wrap input[type="file"] {
            max-width: 100% !important;
            width: 100%;
        }

        /* tombol download melebar */
        .d-flex.align-items-center.flex-wrap > a.btn {
            width: 100%;
        }

        /* tombol import juga melebar */
        .d-flex.align-items-center.flex-wrap button[type="submit"] {
            width: 100%;
        }
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

        function customMatcher(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }

            if (typeof data.text === 'undefined') {
                return null;
            }

            let searchTerm = params.term.replace(/\s+/g, '').toLowerCase();
            let text = data.text.replace(/\s+/g, '').toLowerCase();

            // MATCH NORMAL
            if (text.indexOf(searchTerm) > -1) {
                return data;
            }

            // MATCH TERBALIK (jika user kelebihan huruf)
            if (searchTerm.indexOf(text) > -1) {
                return data;
            }

            return null;
        }

        $('.select2-product').select2({
            placeholder: '-- Pilih Produk --',
            allowClear: true,
            width: '100%',
            matcher: customMatcher
        });

    });
    </script>

    @yield('script')
</body>

</html>