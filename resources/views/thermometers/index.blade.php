@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Master Data Thermometer</h5>
             <a href="{{ route('thermometers.create') }}" class="btn btn-primary btn-sm">+ Tambah Thermometer</a>
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
                        @foreach($thermometers as $item)
                            <tr>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->brand ?? '-' }}</td>
                                <td>{{ $item->area->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('thermometers.edit', $item->uuid) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('thermometers.destroy', $item->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $thermometers->links('pagination::bootstrap-5') }}
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
