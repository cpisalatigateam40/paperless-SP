@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Pemeriksaan Barang Mudah Pecah (Glass & Brittle Plastic)', 'url' => route('report-fragile-item.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-3">Tambah Pemeriksaan Barang Mudah Pecah (Glass & Brittle Plastic)</h5>
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

                <div class="form-check mb-3 mt-3">
                    <input type="checkbox" class="form-check-input" id="inputManualBarang">
                    <label class="form-check-label" for="inputManualBarang">Input Manual Barang</label>
                </div>

                <div id="manualBarangSection" style="display:none;">
                    <div id="manualItemsWrapper"></div>
                    <button type="button" class="btn btn-secondary btn-sm" id="addManualItem">
                        + Tambah Detail Manual Barang
                    </button>
                </div>

                <template id="manualItemTemplate">
                    <div class="manual-item-block border rounded p-3 mb-3 position-relative">
                        <button type="button" class="btn btn-sm btn-danger remove-manual-item"
                            style="position:absolute; top:10px; right:10px;">&times;</button>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Area Manual <span class="text-danger">*</span></label>
                                <select name="manual_items[__INDEX__][section_uuid]" class="form-control">
                                    <option value="">Pilih Area</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Sub Area Manual</label>
                                <input type="text" name="manual_items[__INDEX__][sub_area]" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Nama Barang Manual <span class="text-danger">*</span></label>
                                <input type="text" name="manual_items[__INDEX__][item_name]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Jumlah Manual <span class="text-danger">*</span></label>
                                <input type="number" name="manual_items[__INDEX__][quantity]" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Kondisi Manual <span class="text-danger">*</span></label>
                                <select name="manual_items[__INDEX__][condition]" class="form-control">
                                    <option value="">Pilih Kondisi</option>
                                    <option value="baik">Baik</option>
                                    <option value="rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Nama Karyawan</label>
                                <input type="text" name="manual_items[__INDEX__][employee_name]" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Temuan Ketidaksesuaian Manual</label>
                                <textarea name="manual_items[__INDEX__][issue_notes]" class="form-control"
                                    placeholder="Masukkan temuan (opsional)"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Tindakan Koreksi Manual</label>
                                <textarea name="manual_items[__INDEX__][corrective_action]" class="form-control"
                                    placeholder="Masukkan tindakan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                <button class="btn btn-success">Simpan</button>
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

let manualIndex = 0;

document.getElementById('inputManualBarang').addEventListener('change', function() {
    document.getElementById('manualBarangSection').style.display = this.checked ? 'block' : 'none';
    if (this.checked && document.querySelectorAll('#manualItemsWrapper .manual-item-block').length === 0) {
        addManualItemBlock();
    }
});

document.getElementById('addManualItem').addEventListener('click', addManualItemBlock);

function addManualItemBlock() {
    const html = document.getElementById('manualItemTemplate').innerHTML.replaceAll('__INDEX__', manualIndex);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    document.getElementById('manualItemsWrapper').appendChild(wrapper.firstElementChild);
    manualIndex++;
}

document.getElementById('manualItemsWrapper').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-manual-item')) {
        e.target.closest('.manual-item-block').remove();
    }
});
</script>
@endsection