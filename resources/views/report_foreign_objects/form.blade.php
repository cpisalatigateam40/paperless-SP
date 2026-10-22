@php
    $isEdit = isset($report);
    $blocks = ($isEdit && $report->details->isNotEmpty()) ? $report->details->values() : collect([null]);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
    action="{{ $isEdit ? route('report-foreign-objects.update', $report->uuid) : route('report-foreign-objects.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- INFORMASI LAPORAN --}}
    <div class="fo-detail mb-4">
        <div class="fo-detail-head">
            <span class="fo-detail-title">Informasi Laporan</span>
        </div>
        <div class="fo-detail-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fo-label">Tanggal <span class="req">*</span></label>
                    <input type="date" name="date" class="form-control" required
                        value="{{ old('date', $isEdit ? $report->date->toDateString() : \Carbon\Carbon::today()->toDateString()) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Lokasi <span class="req">*</span></label>
                    <select name="section_uuid" class="form-control select2-detail" required>
                        <option value="">Pilih lokasi...</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->uuid }}"
                                @selected(old('section_uuid', $isEdit ? $report->section_uuid : null) == $section->uuid)>
                                {{ $section->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Shift</label>
                    @if (auth()->user()->hasRole('QC Inspector') && ! $isEdit)
                        <input type="text" class="form-control" readonly
                            value="{{ session('shift_number') }}-{{ session('shift_group') }}">
                    @else
                        <input type="text" name="shift" class="form-control" placeholder="NON-SHIFT"
                            value="{{ old('shift', $isEdit ? $report->shift : '') }}">
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL --}}
    <div id="details-wrapper">
        @foreach ($blocks as $i => $detail)
            @include('report_foreign_objects._detail_fields', ['index' => $i, 'detail' => $detail])
        @endforeach
    </div>

    {{-- Template untuk "Tambah Detail" (inert, tidak ikut ter-submit) --}}
    <template id="detail-template">
        @include('report_foreign_objects._detail_fields', ['index' => '__INDEX__', 'detail' => null])
    </template>

    <button type="button" class="btn btn-outline-primary mb-4" onclick="addDetail()">
        + Tambah Detail
    </button>

    {{-- CATATAN --}}
    <div class="fo-detail mb-4">
        <div class="fo-detail-body">
            <label class="form-label fo-label">Catatan Laporan</label>
            <textarea name="notes" class="form-control" rows="2"
                placeholder="Catatan tambahan untuk laporan ini (opsional)">{{ old('notes', $isEdit ? $report->notes : '') }}</textarea>
        </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="fo-actions">
        <a href="{{ route('report-foreign-objects.index') }}" class="btn btn-light border">Kembali</a>
        <button type="submit" class="btn btn-success px-4">Simpan</button>
    </div>
</form>