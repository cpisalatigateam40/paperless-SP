@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <h4>Master Standar Steamer</h4>

            <div class="d-flex">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('steamer-standards.index') }}" class="mr-3">
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

                <a href="{{ route('steamer-standards.create') }}" class="btn btn-primary">+ Tambah Standar</a>
            </div>
            
        </div>

        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Area</th>
                            <th>Suhu Ruang (°C)</th>
                            <th>Setup Time (menit)</th>
                            <th>Core Temp (°C)</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($steamerStandards as $standard)
                        <tr>
                            <td>{{ $standard->product->product_name ?? '-' }}</td>
                            <td>{{ $standard->area->name ?? '-' }}</td>
                            <td>{{ $standard->room_temp_min }} - {{ $standard->room_temp_max }}</td>
                            <td>{{ $standard->setup_time_min }} - {{ $standard->setup_time_max }}</td>
                            <td>{{ $standard->core_temp_min }} - {{ $standard->core_temp_max }}</td>
                            <td>
                                <a href="{{ route('steamer-standards.edit', $standard->uuid) }}"
                                    class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('steamer-standards.destroy', $standard->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus standar ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data standar steamer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $steamerStandards->links() }}
            </div>

        </div>

    </div>

</div>
@endsection