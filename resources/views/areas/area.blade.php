@extends('layouts.app') {{-- Gunakan layout utama kamu --}}

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class=" text-gray-800">Data Area</h5>
            <a href="{{ route('areas.create') }}" class="btn btn-primary btn-sm">
                Tambah Area
            </a>
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
                <table class="table table-bordered" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areas as $index => $area)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $area->name }}</td>
                            <td>
                                <a href="{{ route('areas.edit', $area->uuid) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('areas.destroy', $area->uuid) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus area ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $areas->links('pagination::bootstrap-5') }}
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
</script>
@endsection