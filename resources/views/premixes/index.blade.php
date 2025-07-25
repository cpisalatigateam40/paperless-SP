@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Master Data Premix</h5>

            <a href="{{ route('premixes.create') }}" class="btn btn-primary btn-sm">Tambah Premix</a>
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
                        <th class="align-middle">Kode Produksi</th>
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
                        <td class="align-middle">{{ $premix->production_code }}</td>
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
</script>
@endsection