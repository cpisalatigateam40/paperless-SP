<style>
    .wb-card .card-header { background: #f8f9fc; }
    .wb-table thead th    { background: #f8f9fc; font-size: .8rem; }
    .wb-table .wb-sub     { font-weight: 500; color: #6c757d; }
    .wb-table td          { vertical-align: middle; }
    .wb-set               { background: #fafafa; font-weight: 500; white-space: nowrap; }
    .wb-no {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; margin-right: 6px; border-radius: 50%;
        background: #e8ebff; color: #4e5bd8; font-size: .7rem; font-weight: 700;
    }
    /* di luar standar master */
    .is-out-std, .is-out-std:focus {
        background-color: #fff1f1 !important;
        border-color: #f1aeb5 !important;
        color: #dc3545 !important;
        font-weight: 700;
    }
    .is-out-std:focus { box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .15) !important; }
    .wb-legend .dot {
        display: inline-block; width: 8px; height: 8px; border-radius: 50%;
        background: #dc3545; margin-right: 4px;
    }
</style>

@csrf

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@php
    $isEdit     = isset($report) && $report;
    $isQc       = auth()->user()->hasRole('QC Inspector');
    $dateValue  = old('date', $isEdit && $report->date ? \Carbon\Carbon::parse($report->date)->format('Y-m-d') : null);
    $shiftValue = ($isQc && !$isEdit)
        ? session('shift_number') . '-' . session('shift_group')
        : old('shift', $report?->shift ?? 'NON-SHIFT');

    $runModels = $isEdit ? $report->runs->values() : collect();
    $runIdx    = old('runs')
        ? array_keys(old('runs'))
        : ($runModels->isNotEmpty() ? $runModels->keys()->all() : [0]);

    $batchCode = old('batch_code', $isEdit ? $report->batches->first()?->batch_code : null);

    $formVersion = $formVersion ?? ($report?->form_version ?? 2);
    $runView     = $formVersion >= 3 ? 'report_waterbaths.v2._run_cikande' : 'report_waterbaths.v2._run';
@endphp

{{-- A. Informasi Produk --}}
<div class="card wb-card mb-4">
    <div class="card-header fw-semibold">
        <i class="fas fa-box-open text-primary mr-2"></i>Informasi Produk
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" required value="{{ $dateValue }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Shift</label>
                @if($isQc || $isEdit)
                    <input type="text" class="form-control" value="{{ $shiftValue }}" readonly>
                @else
                    <input type="text" name="shift" class="form-control" value="{{ $shiftValue }}"
                        placeholder="Mis. 1A / NON-SHIFT">
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Produk</label>
                <select name="product_uuid" class="form-control select2-product" required
                    data-placeholder="-- Pilih Produk --">
                    <option value=""></option>
                    @foreach($products as $product)
                    <option value="{{ $product->uuid }}"
                        @selected(old('product_uuid', $selectedProduct ?? null) == $product->uuid)>
                        {{ $product->product_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Gramasi</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="gramase" class="form-control"
                        placeholder="Mis. 65.00" value="{{ old('gramase', $report?->gramase) }}">
                    <span class="input-group-text">gr</span>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Kode Produk</label>
                <input type="text" name="batch_code" class="form-control"
                    placeholder="Mis. QF27801AA0" value="{{ $batchCode }}">
            </div>
        </div>
    </div>
</div>

@if($formVersion < 3)
<div id="master-alert" class="alert alert-warning d-none">
    Master waterbath untuk produk ini belum ada, jadi standar (Set / Std) tidak terisi otomatis.
</div>
@endif

{{-- B. Hasil Verifikasi (+ Pasteurisasi Ulang) --}}
@if($formVersion < 3)
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 text-muted">Hasil Verifikasi</h6>
    <small class="text-muted wb-legend"><span class="dot"></span>Angka merah = di luar standar master</small>
</div>
@endif
<div id="runs"
    data-master="{{ $formVersion >= 3 ? 0 : 1 }}"
    data-edit="{{ $isEdit ? 1 : 0 }}"
    data-next="{{ max($runIdx) + 1 }}"
    data-product="{{ old('product_uuid', $report?->product_uuid) }}">
    @foreach($runIdx as $i)
        @include($runView, ['i' => $i, 'run' => $runModels->get($i)])
    @endforeach
</div>

<button type="button" id="btn-add-run" class="btn btn-outline-warning mb-3">
    + Tambah Pasteurisasi Ulang
</button>

{{-- Template (inert, tidak ikut ter-submit) --}}
<template id="run-template">
    @include($runView, ['i' => '__INDEX__', 'run' => null])
</template>

<div>
    <a href="{{ route('report_waterbaths.index') }}" class="btn btn-secondary">Kembali</a>
    <button type="submit" class="btn btn-success">Simpan</button>
</div>