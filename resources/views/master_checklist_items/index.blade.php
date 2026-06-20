@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Master Item Checklist</h4>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- 🔍 SEARCH --}}
                <form method="GET" action="{{ route('master_checklist_items.index') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control" placeholder="Cari item..."
                        value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search'))
                    <a href="{{ route('master_checklist_items.index') }}" class="btn btn-danger" title="Reset Filter">
                        Reset
                    </a>
                    @endif
                </form>

                <a href="{{ route('master_checklist_items.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Item
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Kategori</th>
                            <th class="align-middle">Nama Item</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $item)
                        <tr>
                            <td class="align-middle">{{ $i + $items->firstItem() }}</td>
                            <td class="align-middle">{{ $item->category ?? '-' }}</td>
                            <td class="align-middle">{{ $item->name }}</td>
                            <td class="align-middle">{{ $item->area->name ?? '-' }}</td>
                            <td class="align-middle text-center">
                                <a href="{{ route('master_checklist_items.edit', $item->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Item">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('master_checklist_items.destroy', $item->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus item ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada item checklist.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links('pagination::bootstrap-5') }}
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
    }, 3000);
});
</script>
@endsection