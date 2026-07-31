@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-3">Edit Laporan Verifikasi Barang Mudah Pecah (Tahap 2)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report-fragile-item.update-next', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row" style="margin-bottom: 2rem;">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}">
                    </div>
                    <div class="col-md-2">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}">
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
                                                id="checkAllStart-{{ Str::slug($section) }}">
                                            <label for="checkAllStart-{{ Str::slug($section) }}"
                                                class="form-check-label">Check All Waktu Awal</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all-time-end"
                                                data-section="{{ Str::slug($section) }}"
                                                id="checkAllEnd-{{ Str::slug($section) }}">
                                            <label for="checkAllEnd-{{ Str::slug($section) }}"
                                                class="form-check-label">Check All Waktu Akhir</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all"
                                                data-section="{{ Str::slug($section) }}"
                                                id="checkAll-{{ Str::slug($section) }}">
                                            <label class="form-check-label" for="checkAll-{{ Str::slug($section) }}"
                                                style="cursor: pointer;">Check All Notes</label>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @foreach ($items as $item)
                        @php
                            $detail = $report->details->where('fragile_item_uuid', $item->uuid)->first();
                        @endphp
                        <tr>
                            <td class="align-middle">{{ $no++ }}</td>
                            <td class="align-middle">
                                {{ $item->item_name }}
                                <input type="hidden" name="items[{{ $item->uuid }}][fragile_item_uuid]"
                                    value="{{ $item->uuid }}">
                            </td>
                            <td class="align-middle">{{ $item->owner }}</td>
                            <td class="align-middle">{{ $item->quantity }}</td>

                            {{-- Waktu Awal --}}
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][time_start]" value="0">
                                <input type="checkbox" name="items[{{ $item->uuid }}][time_start]" value="1"
                                    class="check-time-start check-time-start-{{ Str::slug($section) }}"
                                    {{ $detail && $detail->time_start ? 'checked' : '' }}>
                            </td>

                            {{-- Waktu Akhir --}}
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][time_end]" value="0">
                                <input type="checkbox" name="items[{{ $item->uuid }}][time_end]" value="1"
                                    class="check-time-end check-time-end-{{ Str::slug($section) }}"
                                    {{ $detail && $detail->time_end ? 'checked' : '' }}>
                            </td>

                            {{-- Notes --}}
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $item->uuid }}][notes]" value="0">
                                <input type="checkbox" name="items[{{ $item->uuid }}][notes]" value="1"
                                    class="check-item check-{{ Str::slug($section) }}"
                                    {{ $detail && $detail->notes ? 'checked' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 mb-3">
                    <strong>Input Manual Barang</strong>
                </div>

                <div id="manualItemsWrapper">
                    @foreach ($report->detailManuals as $manual)
                    <div class="manual-item-block border rounded p-3 mb-3 position-relative">
                        <input type="hidden" name="manual_items[{{ $loop->index }}][uuid]" value="{{ $manual->uuid }}">
                        <button type="button" class="btn btn-sm btn-danger remove-manual-item d-none"
                            style="position:absolute; top:10px; right:10px;">&times;</button>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Area Manual <span class="text-danger">*</span></label>
                                <select name="manual_items[{{ $loop->index }}][section_uuid]" class="form-control">
                                    <option value="">Pilih Area</option>
                                    @foreach ($sections as $section)
                                    <option value="{{ $section->uuid }}" {{ $manual->section_uuid == $section->uuid ? 'selected' : '' }}>
                                        {{ $section->section_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Sub Area Manual</label>
                                <input type="text" name="manual_items[{{ $loop->index }}][sub_area]" class="form-control"
                                    value="{{ $manual->sub_area }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Nama Barang Manual <span class="text-danger">*</span></label>
                                <input type="text" name="manual_items[{{ $loop->index }}][item_name]" class="form-control"
                                    value="{{ $manual->item_name }}">
                            </div>
                            <div class="col-md-6">
                                <label>Jumlah Manual <span class="text-danger">*</span></label>
                                <input type="number" name="manual_items[{{ $loop->index }}][quantity]" class="form-control"
                                    value="{{ $manual->quantity }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Kondisi Manual <span class="text-danger">*</span></label>
                                <select name="manual_items[{{ $loop->index }}][condition]" class="form-control">
                                    <option value="">Pilih Kondisi</option>
                                    <option value="baik" {{ $manual->condition == 'baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="rusak" {{ $manual->condition == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Nama Karyawan</label>
                                <input type="text" name="manual_items[{{ $loop->index }}][employee_name]" class="form-control"
                                    value="{{ $manual->employee_name }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Temuan Ketidaksesuaian Manual</label>
                                <textarea name="manual_items[{{ $loop->index }}][issue_notes]" class="form-control"
                                    placeholder="Masukkan temuan (opsional)">{{ $manual->issue_notes }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Tindakan Koreksi Manual</label>
                                <textarea name="manual_items[{{ $loop->index }}][corrective_action]" class="form-control"
                                    placeholder="Masukkan tindakan (opsional)">{{ $manual->corrective_action }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary btn-sm" id="addManualItem">
                    + Tambah Detail Manual Barang
                </button>

                <div id="deletedManualWrapper"></div>

                <template id="manualItemTemplate">
                    <div class="manual-item-block border rounded p-3 mb-3 position-relative">
                        <button type="button" class="btn btn-sm btn-danger remove-manual-item d-none"
                            style="position:absolute; top:10px; right:10px;">&times;</button>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Area Manual <span class="text-danger">*</span></label>
                                <select name="manual_items[__INDEX__][section_uuid]" class="form-control">
                                    <option value="">Pilih Area</option>
                                    @foreach ($sections as $section)
                                    <option value="{{ $section->uuid }}">{{ $section->name }}</option>
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

                <a href="{{ url()->previous() }}" class="btn btn-secondary mt-4">Kembali</a>
                <button class="btn btn-success mt-4">Edit Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Check All Waktu Awal
    document.querySelectorAll('.check-all-time-start').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const section = this.dataset.section;
            const checkboxes = document.querySelectorAll('.check-time-start-' + section);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Check All Waktu Akhir
    document.querySelectorAll('.check-all-time-end').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const section = this.dataset.section;
            const checkboxes = document.querySelectorAll('.check-time-end-' + section);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Check All Notes
    document.querySelectorAll('.check-all').forEach(function(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const section = this.dataset.section;
            const checkboxes = document.querySelectorAll('.check-' + section);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    let manualIndex = {{ $report->detailManuals->count() }};

    document.getElementById('addManualItem').addEventListener('click', function() {
        const html = document.getElementById('manualItemTemplate').innerHTML.replaceAll('__INDEX__', manualIndex);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        document.getElementById('manualItemsWrapper').appendChild(wrapper.firstElementChild);
        manualIndex++;
    });

    document.getElementById('manualItemsWrapper').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-manual-item')) {
            const block = e.target.closest('.manual-item-block');
            const uuidInput = block.querySelector('input[name*="[uuid]"]');

            // kalau item existing (punya uuid), simpan ke deleted list biar dihapus di server
            if (uuidInput && uuidInput.value) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'deleted_manual_uuids[]';
                hidden.value = uuidInput.value;
                document.getElementById('deletedManualWrapper').appendChild(hidden);
            }

            block.remove();
        }
    });
});


</script>
@endsection
