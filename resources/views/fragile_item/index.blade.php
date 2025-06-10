@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Barang Mudah Pecah</h5>
            <a href="{{ route('fragile-item.create') }}" class="btn btn-primary btn-sm">+ Tambah Data</a>
        </div>

        <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Nama Area</th>
                            <th>Pemilik</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fragileItems as $index => $item)
                            <tr>
                                <td>{{ $fragileItems->firstItem() + $index }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->section_name }}</td>
                                <td>{{ $item->owner ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>
                                    <a href="{{ route('fragile-item.edit', $item->uuid) }}" class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('fragile-item.destroy', $item->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
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
        </div>
    </div>


    



    {{ $fragileItems->links() }} <!-- Pagination -->
</div>
@endsection
