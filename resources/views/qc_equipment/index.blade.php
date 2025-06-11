@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Inventaris peralatan QC</h5>
            <a href="{{ route('qc-equipment.create') }}" class="btn btn-primary btn-sm">+ Tambah Data</a>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Nama Area</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($qcEquipments as $index => $item)
                        <tr>
                            <td>{{ $qcEquipments->firstItem() + $index }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->section_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <a href="{{ route('qc-equipment.edit', $item->uuid) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('qc-equipment.destroy', $item->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-4">
                {{ $qcEquipments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    
</div>
@endsection
