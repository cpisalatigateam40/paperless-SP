@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Master Standar Boiling Tank</h5>

        <div class="d-flex" style="gap: .5rem;">
            
            <!-- <select name="area_uuid" class="form-select form-control">
                <option value="">-- Semua Area --</option>
                @foreach($areas as $area)
                    <option value="{{ $area->uuid }}" @selected(request('area_uuid') == $area->uuid)>
                        {{ $area->name }}
                    </option>
                @endforeach
            </select> -->
            
            <a href="{{ route('master_boiling_tank_standards.create') }}" class="btn btn-primary">Tambah Standar</a>
        </div>
    </div>

    <div class="card-body">
        

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Nama Produk</th>
                        <th>Suhu Tangki I (°C)</th>
                        <th>Suhu Tangki II (°C)</th>
                        <th>Berat Mentah (gr)</th>
                        <th>Actual Core Temp (°C)</th>
                        <th>Berat Matang (gr)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $standard)
                        <tr>
                            <td>{{ $standard->area->name ?? '-' }}</td>
                            <td class="text-start">{{ $standard->product->product_name ?? '-' }}</td>
                            <td>{{ $standard->suhu_tangki_1_label ?? '-' }}</td>
                            <td>{{ $standard->suhu_tangki_2_label ?? '-' }}</td>
                            <td>{{ $standard->berat_mentah_label ?? '-' }}</td>
                            <td>{{ $standard->actual_core_temp_label ?? '-' }}</td>
                            <td>{{ $standard->berat_matang_label ?? '-' }}</td>
                            <td>
                                <a href="{{ route('master_boiling_tank_standards.edit', $standard->uuid) }}"
                                   class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('master_boiling_tank_standards.destroy', $standard->uuid) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus standar ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $standards->links('pagination::bootstrap-5') }}
        </div>

        
    </div>
</div>
@endsection