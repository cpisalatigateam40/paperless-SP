@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Data Formula</h4>

            <div class="d-flex align-items-center gap-2" style="gap: .5rem;">
                <form action="{{ route('formulas.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto p-0">
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control form-control-sm"
                                placeholder="Cari formula / produk...">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">
                                Search
                            </button>

                            @if(request('search'))
                                <a href="{{ route('formulas.index') }}"
                                class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: .4rem;">
                    <a href="{{ route('formulas.template') }}" class="btn btn-outline-success btn-sm">
                        Download Template
                    </a>

                    <form action="{{ route('formulas.import') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="d-flex align-items-center gap-2">
                        @csrf

                        <input type="file"
                            name="file"
                            accept=".xlsx,.xls"
                            class="form-control form-control-sm mr-2"
                            style="max-width: 220px"
                            required>

                        <button type="submit" class="btn btn-success btn-sm">
                            Import Excel
                        </button>
                    </form>
                </div>
                <a href="{{ route('formulas.create') }}" class="btn btn-primary btn-sm">Tambah Formula</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Formula</th>
                            <th>Produk</th>
                            <th>Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formulas as $formula)
                        <tr>
                            <td>{{ $formula->formula_name }}</td>
                            <td>{{ $formula->product->product_name ?? '-' }}</td>
                            <td>{{ $formula->area->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('formulas.detail', $formula->uuid) }}" class="btn btn-info btn-sm">Lihat
                                    Formula</a>
                                <form action="{{ route('formulas.destroy', $formula->uuid) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete formula?')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $formulas->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});

function isSimilar(a, b) {
    return a.includes(b) || b.includes(a);
}

$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        let keyword = $(this).val().toLowerCase();
        $('table tbody tr').each(function() {
            let rowText = $(this).text().toLowerCase();
            let isMatch = isSimilar(rowText, keyword);
            $(this).toggle(isMatch);
        });
    });
});
</script>
@endsection