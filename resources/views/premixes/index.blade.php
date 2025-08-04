@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Master Data Premix</h5>

            <div class="d-flex justify-content-between" style="gap: .5rem;">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm"
                        placeholder="Cari Raw Material" style="border-radius: 0;">
                    <span class="input-group-text" style="border-radius: 0;">
                        <i class="fas fa-search"></i>
                    </span>
                </div>

                <form action="{{ route('premixes.import') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex align-items-center gap-2">
                    @csrf
                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv"
                        required style="width: 180px; margin-right: .5rem;">
                    <button type="submit" class="btn btn-success btn-sm">Import</button>
                </form>

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