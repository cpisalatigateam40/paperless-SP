@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-3">Tambah Laporan Verifikasi Barang Mudah Pecah</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report-fragile-item.store') }}" method="POST">
                @csrf
                <div class="row" style="margin-bottom: 2rem;">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>
                </div>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Pemilik</th>
                            <th>Jumlah</th>
                            <th>Waktu Awal</th>
                            <th>Waktu Akhir</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($fragileItems->groupBy('section_name') as $section => $items)
                        <tr>
                            <td colspan="7">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $section }}</strong>

                                    <div style="margin-right: 1rem; display: flex; gap: 3.5rem;">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all-time-start"
                                                data-section="{{ Str::slug($section) }}"
                                                id="checkAllStart-{{ Str::slug($section) }}"
                                                {{ $isEdit ? 'disabled' : '' }}>
                                            <label for="checkAllStart-{{ Str::slug($section) }}"
                                                class="form-check-label">Check All Waktu Awal</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all-time-end"
                                                data-section="{{ Str::slug($section) }}"
                                                id="checkAllEnd-{{ Str::slug($section) }}"
                                                {{ !$isEdit ? 'disabled' : '' }}>
                                            <label for="checkAllEnd-{{ Str::slug($section) }}"
                                                class="form-check-label">Check All Waktu Akhir</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all"
                                                data-section="{{ Str::slug($section) }}"
                                                id="checkAll-{{ Str::slug($section) }}">
                                            <label class="form-check-label" for="checkAll-{{ Str::slug($section) }}"
                                                style="cursor: pointer;">
                                                Check All Notes
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @foreach ($items as $item)
                        <tr>
                            <td class="align-middle">{{ $no++ }}</td>
                            <td class="align-middle">
                                {{ $item->item_name }}
                                <input type="hidden" name="items[{{ $item->uuid }}][fragile_item_uuid]"
                                    value="{{ $item->uuid }}">
                            </td>
                            <td class="align-middle">{{ $item->owner }}</td>
                            <td class="align-middle">{{ $item->quantity }}</td>
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][time_start]" value="0"
                                    {{ $isEdit ? 'disabled' : '' }}>
                                <input type="checkbox" name="items[{{ $item->uuid }}][time_start]" value="1"
                                    class="check-time-start check-time-start-{{ Str::slug($section) }}"
                                    {{ $isEdit ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][time_end]" value="0"
                                    {{ !$isEdit ? 'disabled' : '' }}>
                                <input type="checkbox" name="items[{{ $item->uuid }}][time_end]" value="1"
                                    class="check-time-end check-time-end-{{ Str::slug($section) }}"
                                    {{ !$isEdit ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][notes]" value="0">
                                <input type="checkbox" name="items[{{ $item->uuid }}][notes]" value="1"
                                    class="check-item check-{{ Str::slug($section) }}" style="cursor: pointer;">
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary" style="margin-top: 1rem;">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.check-all').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const sectionClass = 'check-' + this.dataset.section;
            const checkboxes = document.querySelectorAll('.' + sectionClass);

            checkboxes.forEach(function(cb) {
                cb.checked = checkAllBox.checked;
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Time Start Check All
    document.querySelectorAll('.check-all-time-start').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const section = this.dataset.section;
            const checkboxes = document.querySelectorAll('.check-time-start-' + section);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Time End Check All
    document.querySelectorAll('.check-all-time-end').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const section = this.dataset.section;
            const checkboxes = document.querySelectorAll('.check-time-end-' + section);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });
});
</script>
@endsection