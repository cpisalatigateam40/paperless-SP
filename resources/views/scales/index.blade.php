@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Daftar Timbangan</h5>
            <a href="{{ route('scales.create') }}" class="btn btn-sm btn-primary">+ Tambah Timbangan</a>
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
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Jenis</th>
                            <th>Merek</th>
                            <th>Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scales as $scale)
                            <tr>
                                <td>{{ $scale->code }}</td>
                                <td>{{ $scale->type }}</td>
                                <td>{{ $scale->brand }}</td>
                                <td>{{ $scale->area->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('scales.edit', $scale->uuid) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('scales.destroy', $scale->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus timbangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data timbangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $scales->links('pagination::bootstrap-5') }}
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
