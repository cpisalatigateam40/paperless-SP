@extends('layouts.app')

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Verifikasi Proses Pemasakan di Boiling Tank', 'url' => route('report_boiling_tanks.index')],
    ['label' => 'Tambah/Edit Data', 'url' => null],
]" />

<form action="{{ $isEdit ? route('report_boiling_tanks.update', $report->uuid) : route('report_boiling_tanks.store') }}"
      method="POST" id="boilingTankForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header fw-bold">Informasi Produk</div>
        <div class="card-body row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Hari, Tanggal</label>
                <input type="date" name="date" class="form-control"
                       value="{{ old('date', optional($report?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Shift</label>
                <input type="text" class="form-control"
                       value="{{ $report->shift ?? (session('shift_number') . '-' . session('shift_group')) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Produk</label>
                <select name="product_uuid" id="productSelect" class="form-select form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->uuid }}"
                            @selected(old('product_uuid', $report->product_uuid ?? null) == $product->uuid)>
                            {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Kode Produk</label>
                <input type="text" name="product_code" class="form-control"
                       placeholder="mis: QF27801AA0 - QF27807AA0"
                       value="{{ old('product_code', $report->product_code ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Gramasi (gr)</label>
                <input type="number" step="0.01" name="gramasi" class="form-control"
                       value="{{ old('gramasi', $report->gramasi ?? '') }}" placeholder="mis: 500">
            </div>
        </div>

        <div class="card-body row g-3 mb-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Line Boiling Tank</label>
                <input type="text" name="line_boiling_tank" class="form-control"
                    value="{{ old('line_boiling_tank', $report->line_boiling_tank ?? '') }}" placeholder="mis: Line 1">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label">Waktu Proses - Start</label>
                <input type="time" id="waktuProsesStart" class="form-control"
                    value="{{ old('waktu_proses_start', $report->waktu_proses_start ?? '') }}">
                <input type="hidden" name="waktu_proses_start" id="waktuProsesStartHidden"
                    value="{{ old('waktu_proses_start', $report->waktu_proses_start ?? '') }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label">Waktu Proses - End</label>
                <input type="time" id="waktuProsesEnd" class="form-control"
                    value="{{ old('waktu_proses_end', $report->waktu_proses_end ?? '') }}">
                <input type="hidden" name="waktu_proses_end" id="waktuProsesEndHidden"
                    value="{{ old('waktu_proses_end', $report->waktu_proses_end ?? '') }}">
            </div>
        </div>
    </div>

    

    <div id="detailListContainer">
        @forelse(($report->details ?? []) as $detail)
            @include('report_boiling_tanks._detail_row', ['detail' => $detail, 'dKey' => $detail->uuid, 'report' => $report])
        @empty
            @include('report_boiling_tanks._detail_row', ['detail' => null, 'dKey' => 'new0', 'report' => $report])
        @endforelse
    </div>
    
    <button type="button" class="btn btn-sm btn-info mb-3" id="addDetailBtn">+ Tambah Detail Pemeriksaan</button>

    <div class="card mb-3">
        <div class="card-header fw-bold">Catatan & Dokumentasi</div>
        <div class="card-body">
            <label class="form-label">Catatan</label>
            <input type="text" name="link_kurva" class="form-control" placeholder="masukkan catatan">
        </div>
    </div>

    <div class="d-flex gap-2" style="gap: .4rem;">
        <a href="{{ route('report_boiling_tanks.index') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" name="action" value="draft" class="btn btn-warning">Simpan Draft</button>
        <button type="submit" name="action" value="finish" class="btn btn-success">Simpan & Selesaikan</button>
    </div>
</form>

{{-- Template untuk row Kode Produksi baru (di-clone via JS) --}}
<template id="detailRowTemplate">
    @include('report_boiling_tanks._detail_row', ['detail' => null, 'dKey' => '__DKEY__', 'report' => null])
</template>

{{-- Template untuk row Pemeriksaan baru (di-clone via JS) --}}
<template id="checkRowTemplate">
    @include('report_boiling_tanks._check_row', ['check' => null, 'dKey' => '__DKEY__', 'cKey' => '__CKEY__', 'checkNumber' => '__CNUM__', 'locked' => false])
</template>

@push('scripts')
<script src="{{ asset('js/report-boiling-tank-form.js') }}"></script>
@endpush
@endsection