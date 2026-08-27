@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Master Data Metal Detector</h4>

            <div class="d-flex">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('metal_detectors.index') }}" class="mr-3">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area"
                            class="form-select form-control form-control"
                            onchange="this.form.submit()">
                        <option value="">Semua Area</option>

                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endhasanyrole

                <a href="{{ route('metal_detectors.create') }}" class="btn btn-primary">Tambah MD</a>
            </div>
            
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Merk</th>
                        <th>Type/Model</th>
                        <th>No. Series</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($metalDetectors as $md)
                    <tr>
                        <td>{{ $loop->iteration + ($metalDetectors->currentPage() - 1) * $metalDetectors->perPage() }}</td>
                        <td>{{ $md->merk }}</td>
                        <td>{{ $md->type_model }}</td>
                        <td>{{ $md->no_series }}</td>
                        <td>
                            <a href="{{ route('metal_detectors.edit', $md->uuid) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('metal_detectors.destroy', $md->uuid) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data Metal Detector.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $metalDetectors->links() }}
        </div>
    </div>
</div>
@endsection