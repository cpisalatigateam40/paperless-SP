<div class="check-row border rounded p-2 mb-3 mb-lg-2 border-lg-0 rounded-lg-0 p-lg-3" data-ckey="{{ $cKey }}">

    {{-- Header ringkas: cuma tampil di mobile & tablet kecil (<lg) --}}
    <div class="d-flex d-lg-none justify-content-between align-items-center mb-4">
        <span class="badge bg-light text-dark border check-number">Pemeriksaan {{ $checkNumber }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger remove-check-btn">&times;</button>
    </div>

    <input type="hidden" name="details[{{ $dKey }}][checks][{{ $cKey }}][uuid]" value="{{ $check->uuid ?? '' }}">
    <input type="hidden" name="details[{{ $dKey }}][checks][{{ $cKey }}][check_index]" value="{{ $checkNumber }}" class="check-index-input">

    <div class="row g-2 align-items-end flex-lg-nowrap">
        {{-- Badge nomor: cuma tampil di lg ke atas, sejajar dengan input --}}
        <div class="col-auto d-none d-lg-flex align-items-center">
            <span class="badge bg-light text-dark border check-number" style="font-size: 18px !important; margin-bottom: 1.4rem;">{{ $checkNumber }}</span>
        </div>

        <div class="col-6 col-lg mb-3">
            <label class="form-label mb-1">Berat Mentah (gr) <small class="text-muted std-berat-mentah" style="color: red !important;"></small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][checks][{{ $cKey }}][berat_mentah]"
                   class="form-control check-berat-mentah-input" placeholder="mis: 12"
                   value="{{ $check->berat_mentah ?? '' }}">
        </div>
        <div class="col-6 col-lg mb-3">
            <label class="form-label mb-1">Actual Core Temp (°C) <small class="text-muted std-actual-core-temp" style="color: red !important;"></small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][checks][{{ $cKey }}][actual_core_temp]"
                   class="form-control check-actual-core-temp-input" placeholder="mis: 12"
                   value="{{ $check->actual_core_temp ?? '' }}">
        </div>
        <div class="col-6 col-lg mb-3">
            <label class="form-label mb-1">Berat Matang (gr) <small class="text-muted std-berat-matang" style="color: red !important;"></small></label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][checks][{{ $cKey }}][berat_matang]"
                   class="form-control check-berat-matang-input" placeholder="mis: 12"
                   value="{{ $check->berat_matang ?? '' }}">
        </div>
        <div class="col-6 col-lg mb-3">
            <label class="form-label mb-1">Suhu After Cooling (°C)</label>
            <input type="number" step="0.01" name="details[{{ $dKey }}][checks][{{ $cKey }}][suhu_after_cooling]"
                   class="form-control" placeholder="mis: 12"
                   value="{{ $check->suhu_after_cooling ?? '' }}">
        </div>

        {{-- Tombol hapus: cuma tampil di lg ke atas, sejajar dengan input --}}
        <div class="col-auto d-none d-lg-flex">
            <button type="button" class="btn btn-outline-danger remove-check-btn mb-3">&times;</button>
        </div>
    </div>
</div>