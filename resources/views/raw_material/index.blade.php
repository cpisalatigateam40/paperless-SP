@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Raw Material</h5>
            <div class="d-flex align-items-center gap-2" style="gap: .5rem;">
                <form action="{{ route('raw-materials.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto p-0">
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control form-control-sm"
                                placeholder="Cari nama material / supplier">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">
                                Search
                            </button>

                            @if(request('search'))
                                <a href="{{ route('raw-materials.index') }}"
                                class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: .4rem;">
                    <a href="{{ route('raw-materials.template') }}" class="btn btn-outline-success btn-sm">
                        Download Template
                    </a>

                    <form action="{{ route('raw-materials.import') }}"
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
                <a href="{{ route('raw-materials.create') }}" class="btn btn-primary btn-sm">Tambah</a>
            </div>
        </div>


        <div class="card-body">
            <div class="table-responsive">
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
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Material</th>
                            <!-- <th>Produsen</th> -->
                            <th>Area</th>
                            <!-- <th>Batas Kadaluarsa (Bulan)</th> -->
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rawMaterials as $material)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $material->material_name }}</td>
                            <!-- <td>{{ $material->supplier }}</td> -->
                            <td>{{ $material->area->name ?? '-' }}</td>
                            <!-- <td>{{ $material->shelf_life ?? '-' }}</td> -->
                            <td>
                                <a href="{{ route('raw-materials.edit', $material->uuid) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('raw-materials.destroy', $material->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

                <div class="mt-3">
                    {{ $rawMaterials->links('pagination::bootstrap-5') }}
                </div>
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