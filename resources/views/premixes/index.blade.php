@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Master Data Premix</h5>

            <div class="d-flex justify-content-between" style="gap: .5rem;">
                <form action="{{ route('premixes.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto p-0">
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control form-control-sm"
                                placeholder="Cari nama / produsen / kode produksi">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">
                                Search
                            </button>

                            @if(request('search'))
                                <a href="{{ route('premixes.index') }}"
                                class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>


                <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: .4rem;">
                    <a href="{{ route('premixes.template') }}" class="btn btn-outline-success btn-sm">
                        Download Template
                    </a>

                    <form action="{{ route('premixes.import') }}"
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

                <a href="{{ route('premixes.create') }}" class="btn btn-primary btn-sm">Tambah Premix</a>
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
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Nama</th>
                            <!-- <th class="align-middle">Kode Produksi</th> -->
                            <th class="align-middle">Produsen</th>
                            <th class="align-middle">Batas Kadaluarsa (Bulan)</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($premixes as $i => $premix)
                        <tr>
                            <td class="align-middle">{{ $i + $premixes->firstItem() }}</td>
                            <td class="align-middle">{{ $premix->name }}</td>
                            <!-- <td class="align-middle">{{ $premix->production_code }}</td> -->
                            <td class="align-middle">{{ $premix->producer }}</td>
                            <td class="align-middle">{{ $premix->shelf_life ?? '-' }}</td>
                            <td class="align-middle">{{ $premix->area->name ?? '-' }}</td>
                            <td class="align-middle text-center">
                                <a href="{{ route('premixes.edit', $premix->uuid) }}"
                                    class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('premixes.destroy', $premix->uuid) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $premixes->links('pagination::bootstrap-5') }}
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