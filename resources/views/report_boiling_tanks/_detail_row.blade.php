@php
    $checks = $detail->checks ?? collect();
    $checkCount = max($checks->count(), 3);

    $sensoriOptions = ['' => '-- Pilih --', 'OK' => 'OK', 'Tidak OK' => 'Tidak OK'];
@endphp
<div class="detail-card border card rounded p-3 mb-3" data-dkey="{{ $dKey }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold detail-title" style="font-weight: bold;">Detail Pemeriksaan</span>
        <button type="button" class="btn btn-sm btn-outline-danger remove-detail-btn">Hapus</button>
    </div>

    <input type="hidden" name="details[{{ $dKey }}][uuid]" value="{{ $detail->uuid ?? '' }}">

    <div class="row g-2 mb-2">
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">Kode Produksi</label>
            <input type="text" name="details[{{ $dKey }}][kode_produksi]" class="form-control form-control"
                   placeholder="mis: QF27801AA0"
                   value="{{ $detail->kode_produksi ?? '' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">Start</label>
            <input type="time" name="details[{{ $dKey }}][start]"
                   class="form-control form-control detail-start-input"
                   value="{{ $detail->start ?? '' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">End</label>
            <input type="time" name="details[{{ $dKey }}][end]"
                   class="form-control form-control detail-end-input"
                   value="{{ $detail->end ?? '' }}">
        </div>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">Suhu Adonan (°C) <small class="text-muted">Std 16±2°C</small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][suhu_adonan]" class="form-control form-control"
                   placeholder="mis: 12"
                   value="{{ $detail->suhu_adonan ?? '' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">Suhu Air Tangki I (°C) <small class="text-muted">Std 75-85°C</small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][aktual_suhu_tangki_1]" class="form-control form-control"
                   placeholder="mis: 12"
                   value="{{ $detail->aktual_suhu_tangki_1 ?? '' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label  mb-1">Suhu Air Tangki II (°C) <small class="text-muted">Std 85-95°C</small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][aktual_suhu_tangki_2]" class="form-control form-control"
                   placeholder="mis: 12"
                   value="{{ $detail->aktual_suhu_tangki_2 ?? '' }}">
        </div>
    </div>

    <div class="checks-section" data-dkey="{{ $dKey }}">
        <div class="fw-semibold text-muted mb-2">Pemeriksaan</div>
        <div class="checks-list">
            @for($i = 0; $i < $checkCount; $i++)
                @php $check = $checks->get($i); @endphp
                @include('report_boiling_tanks._check_row', [
                    'check' => $check,
                    'dKey' => $dKey,
                    'cKey' => $check->uuid ?? 'new' . $i,
                    'checkNumber' => $i + 1,
                ])
            @endfor
        </div>
        <button type="button" class="btn btn-sm btn-outline-info add-check-btn mt-2 mb-4">+ Tambah Pemeriksaan</button>
    </div>

    <div class="fw-semibold text-muted mb-2">Pemeriksaan Sensori</div>
    <div class="row g-2 mb-3">
        @foreach(['bentuk' => 'Bentuk', 'warna' => 'Warna', 'aroma' => 'Aroma', 'rasa' => 'Rasa', 'tekstur' => 'Tekstur'] as $field => $label)
            <div class="col-6 col-sm-4 col-lg">
                <label class="form-label mb-1">{{ $label }}</label>
                <select name="details[{{ $dKey }}][sensori_{{ $field }}]" class="form-select form-control">
                    @foreach($sensoriOptions as $value => $optionLabel)
                        <option value="{{ $value }}"
                            @selected(($detail->{'sensori_' . $field} ?? 'OK') === $value)>
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</div>