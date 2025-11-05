@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Barang Mudah Pecah</h5>

            <div class="d-flex align-items-center gap-2" style="gap: .5rem;">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm"
                        placeholder="Cari barang mudah pecah" style="border-radius: 0;">
                    <span class="input-group-text" style="border-radius: 0;">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <a href="{{ route('fragile-item.create') }}" class="btn btn-primary btn-sm">+ Tambah Data</a>
            </div>
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
                            <a href="{{ route('fragile-item.edit', $item->uuid) }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ route('fragile-item.destroy', $item->uuid) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
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
                {{ $fragileItems->links('pagination::bootstrap-5') }}
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

function isSimilar(a, b) {
    return a.includes(b) || b.includes(a);
}

$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        let keyword = $(this).val().toLowerCase();
        $('table tbody tr').each(function() {
            let rowText = $(this).text().toLowerCase();
            let isMatch = isSimilar(rowText, keyword);
            $(this).toggle(isMatch);
        });
    });
});
</script>
@endsection