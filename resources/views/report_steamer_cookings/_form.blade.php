<style>
.core-temp-item {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}
.core-temp-item:last-of-type {
    margin-bottom: 8px;
}
.core-temp-badge {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #495057;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
.core-temp-item input {
    flex: 1;
    min-width: 0;
    border-radius: 6px;
}
.core-temp-remove {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    padding: 0;
    border-radius: 50%;
    border: 1px solid #f1aeb5;
    background-color: #fff;
    color: #dc3545;
    font-size: 13px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
.core-temp-remove:hover {
    background-color: #dc3545;
    color: #fff;
}
.core-temp-add-btn {
    border-radius: 6px;
    font-size: 11px;
    padding: 3px 10px;
}
</style>

@php
    $isEdit = isset($report);
    $initialBatchIndex = $isEdit ? $report->batches->count() : 1;
@endphp

{{-- A. INFORMASI PRODUK --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Tanggal</label>
        <input type="date" name="date" class="form-control"
            value="{{ old('date', $isEdit ? \Carbon\Carbon::parse($report->date)->format('Y-m-d') : \Carbon\Carbon::today()->toDateString()) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label>Shift</label>
        <input type="text" name="shift" class="form-control"
            value="{{ old('shift', $isEdit ? $report->shift : session('shift_number').'-'.session('shift_group')) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label>Nama Produk</label>
        <select name="product_uuid" id="product_uuid" class="form-control select2-product" required>
            <option value="">-- pilih produk --</option>
            @foreach($products as $product)
                <option value="{{ $product->uuid }}"
                    {{ old('product_uuid', $isEdit ? $report->product_uuid : '') == $product->uuid ? 'selected' : '' }}>
                    {{ $product->product_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Gramasi</label>
        <input type="number" step="0.01" name="gramase" class="form-control"
            value="{{ old('gramase', $isEdit ? $report->gramase : '') }}" placeholder="mis: 250">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Kode Produk</label>
        <input type="text" name="product_code_range" class="form-control" placeholder="ex: QF27801AA0 - QF27807AA0"
            value="{{ old('product_code_range', $isEdit ? $report->product_code_range : '') }}">
    </div>
</div>

<div id="standardInfo" class="alert alert-info d-none mb-3">
    Standar produk &mdash;
    Suhu Ruang: <strong id="std_room_temp">-</strong>°C |
    Setup Time: <strong id="std_setup_time">-</strong> menit |
    Core Temp: <strong id="std_core_temp">-</strong>°C
</div>

<div class="mb-4">
    <label>Catatan</label>
    <textarea name="notes" class="form-control" rows="2" placeholder="masukkan catatan (opsional)">{{ old('notes', $isEdit ? $report->notes : '') }}</textarea>
</div>

<hr>
<h5 class="mb-3">B. Hasil Verifikasi Cooking</h5>

<div id="batchWrapper">
@if($isEdit)
    @foreach($report->batches as $bIndex => $batch)
        <div class="batch-block card" data-batch-index="{{ $bIndex }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Steamer Batch #{{ $bIndex + 1 }}</strong>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeBatch(this)">Hapus Batch</button>
            </div>
            <div class="card-body">
                <input type="hidden" name="batches[{{ $bIndex }}][uuid]" value="{{ $batch->uuid }}">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Nomor Steamer</label>
                        <input type="text" name="batches[{{ $bIndex }}][steamer_number]" class="form-control" value="{{ $batch->steamer_number }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Jumlah Trolly</label>
                        <input type="number" name="batches[{{ $bIndex }}][trolley_count]" class="form-control" value="{{ $batch->trolley_count }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Tray/Trolly</label>
                        <input type="number" name="batches[{{ $bIndex }}][tray_per_trolley]" class="form-control" value="{{ $batch->tray_per_trolley }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Waktu Proses</label>
                        <div class="d-flex g-2">
                            <input type="time" name="batches[{{ $bIndex }}][start_time]" class="form-control mr-3" value="{{ $batch->start_time }}">
                            <input type="time" name="batches[{{ $bIndex }}][end_time]" class="form-control" value="{{ $batch->end_time }}">
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-sm align-middle detail-table">
                    <thead>
                        <tr>
                            <th style="min-width:120px">Kode Produksi</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Setup (mnt)</th>
                            <th>Suhu Ruang</th>
                            <th style="min-width:140px">Core Temp</th>
                            <th>Bentuk</th>
                            <th>Warna</th>
                            <th>Aroma</th>
                            <th>Rasa</th>
                            <th>Tekstur</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="detail-wrapper" data-batch-index="{{ $bIndex }}" data-detail-counter="{{ $batch->details->count() }}">
                        @foreach($batch->details as $dIndex => $detail)
                            <tr class="detail-row" data-detail-index="{{ $dIndex }}">
                                <input type="hidden" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][uuid]" value="{{ $detail->uuid }}">
                                <td><input type="text" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][production_code]" class="form-control form-control-sm" value="{{ $detail->production_code }}"></td>
                                <td><input type="time" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][start_process]" class="form-control form-control-sm" value="{{ $detail->start_process }}"></td>
                                <td><input type="time" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][end_process]" class="form-control form-control-sm" value="{{ $detail->end_process }}"></td>
                                <td><input type="number" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][setup_time]" class="form-control form-control-sm setup-time-input" value="{{ $detail->setup_time }}"></td>
                                <td><input type="number" step="0.01" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][room_temp]" class="form-control form-control-sm room-temp-input" value="{{ $detail->room_temp }}"></td>
                                <td class="core-temp-wrapper">
                                    @foreach($detail->coreTemps as $seq => $ct)
                                        <div class="core-temp-item">
                                            <span class="core-temp-badge">{{ $seq + 1 }}</span>
                                            <input type="number" step="0.01" name="batches[{{ $bIndex }}][details][{{ $dIndex }}][core_temps][]" class="form-control form-control-sm" value="{{ $ct->temp_value }}" placeholder="°C">
                                            <button type="button" class="core-temp-remove" onclick="this.closest('.core-temp-item').remove()" title="Hapus titik">&times;</button>
                                        </div>
                                    @endforeach
                                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addCoreTemp(this)">+ Titik</button>
                                </td>
                                @foreach(['sensory_bentuk','sensory_warna','sensory_aroma','sensory_rasa','sensory_tekstur'] as $field)
                                    <td>
                                        <select name="batches[{{ $bIndex }}][details][{{ $dIndex }}][{{ $field }}]" class="form-control form-control-sm">
                                            <option value="OK" {{ $detail->$field == 'OK' ? 'selected' : '' }}>OK</option>
                                            <option value="Tidak OK" {{ $detail->$field == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                        </select>
                                    </td>
                                @endforeach
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeDetail(this)">&times;</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="addDetail(this)">+ Tambah Baris</button>
            </div>
        </div>
    @endforeach
@else
    <div class="batch-block card mb-4" data-batch-index="0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Steamer Batch #1</strong>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBatch(this)">Hapus Batch</button>
        </div>
        <div class="card-body">
            <input type="hidden" name="batches[0][uuid]" value="">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label>Nomor Steamer</label>
                    <input type="text" name="batches[0][steamer_number]" class="form-control" placeholder="mis: 1">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Jumlah Trolly</label>
                    <input type="number" name="batches[0][trolley_count]" class="form-control" placeholder="mis: 2">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tray/Trolly</label>
                    <input type="number" name="batches[0][tray_per_trolley]" class="form-control" placeholder="mis: 20">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Waktu Proses</label>
                    <div class="d-flex gap-1" style="gap: .4rem;">
                        <input type="time" name="batches[0][start_time]" class="form-control">
                        <input type="time" name="batches[0][end_time]" class="form-control">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle detail-table">
                    <thead>
                        <tr>
                            <th style="min-width:120px">Kode Produksi</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Setup (mnt)</th>
                            <th>Suhu Ruang</th>
                            <th style="min-width:140px">Core Temp</th>
                            <th>Bentuk</th>
                            <th>Warna</th>
                            <th>Aroma</th>
                            <th>Rasa</th>
                            <th>Tekstur</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="detail-wrapper" data-batch-index="0" data-detail-counter="1">
                        <tr class="detail-row" data-detail-index="0">
                            <input type="hidden" name="batches[0][details][0][uuid]" value="">
                            <td><input type="text" name="batches[0][details][0][production_code]" class="form-control form-control-sm" placeholder="mis: QF27801AA0"></td>
                            <td><input type="time" name="batches[0][details][0][start_process]" class="form-control form-control-sm"></td>
                            <td><input type="time" name="batches[0][details][0][end_process]" class="form-control form-control-sm"></td>
                            <td><input type="number" name="batches[0][details][0][setup_time]" class="form-control form-control-sm setup-time-input" placeholder="mis: 12"></td>
                            <td><input type="number" step="0.01" name="batches[0][details][0][room_temp]" class="form-control form-control-sm room-temp-input" placeholder="mis: 12"></td>
                            <td class="core-temp-wrapper">
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addCoreTemp(this)">+ Titik</button>
                            </td>
                            @foreach(['sensory_bentuk','sensory_warna','sensory_aroma','sensory_rasa','sensory_tekstur'] as $field)
                                <td>
                                    <select name="batches[0][details][0][{{ $field }}]" class="form-control form-control-sm">
                                        <option value="OK">OK</option>
                                        <option value="Tidak OK">Tidak OK</option>
                                    </select>
                                </td>
                            @endforeach
                            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeDetail(this)">&times;</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="addDetail(this)">+ Tambah Baris</button>
        </div>
    </div>
@endif
</div>

<button type="button" class="btn btn-success btn-sm mb-3 mt-3" onclick="addBatch()">+ Tambah Steamer / Batch</button>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('report_steamer_cookings.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>