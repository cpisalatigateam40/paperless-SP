@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Master Data Benda Tajam</h4>
            <a href="{{ route('sharp_tools.create') }}" class="btn btn-primary btn-sm">Tambah</a>
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
                        <th class="align-middle text-center">No</th>
                        <th class="align-middle">Nama</th>
                        <th class="align-middle text-center">Jumlah</th>
                        <th class="align-middle">Area</th>
                        <th class="align-middle text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sharpTools as $tool)
                    <tr>
                        <td class="align-middle text-center">{{ $loop->iteration }}</td>
                        <td class="align-middle">{{ $tool->name }}</td>
                        <td class="align-middle text-center">{{ $tool->quantity }}</td>
                        <td class="align-middle">{{ $tool->area->name ?? '-' }}</td>
                        <td class="align-middle text-center">
                            <a href="{{ route('sharp_tools.edit', $tool->uuid) }}"
                                class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('sharp_tools.destroy', $tool->uuid) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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