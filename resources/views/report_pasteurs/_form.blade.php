@php
    $report ??= null;
    $isAudit = (bool) ($report?->is_audit);
    $steps   = \App\Models\MasterPasteurStep::STEPS;
    $details = $report ? $report->details->values() : collect([null]);
@endphp

@once
<style>
    .pasteur-form .section-label { font-size: .75rem; letter-spacing: .04em; text-transform: uppercase; color: #8a8d99; font-weight: 600; }

    /* putaran: pertama biru, pasteurisasi ulang kuning */
    .pasteur-form .run-item { border-left: 4px solid #696cff; background: #fff; }
    .pasteur-form .runs-wrapper .run-item:not(:first-child) { border-left-color: #ffab00; }

    .pasteur-form .time-box { background: #f8f9fb; border: 1px dashed #ced4da; border-radius: .5rem; }
    .pasteur-form .total-badge { background: #eef0ff; color: #4c51bf; border-radius: 2rem; padding: .35rem .9rem; font-weight: 600; display: inline-block; }

    .pasteur-form .table-steps thead th { background: #f1f3f9; font-size: .78rem; vertical-align: middle; }
    .pasteur-form .table-steps .grp { border-bottom: 0; }
    .pasteur-form .table-steps .col-set { background: #f8f9fb; color: #6c757d; font-weight: 500; width: 110px; white-space: nowrap; }
    .pasteur-form .table-steps .step-no {
        width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 600; background: #eef0ff; color: #4c51bf; margin-right: .5rem;
    }
    .pasteur-form .table-steps tr.finish-row td { background: #f8f9fb; }

    /* di luar standar */
    .pasteur-form .form-control.out-of-std { border-color: #dc3545; background-color: #fff5f5; }

    .pasteur-form .action-bar {
        position: sticky; bottom: 0; z-index: 5; background: #fff;
        border-top: 1px solid #e6e8f0; box-shadow: 0 -4px 12px rgba(0,0,0,.04);
    }
</style>
@endonce

<div class="pasteur-form">
@csrf

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Header laporan --}}
<div class="card border mb-4">
    <div class="card-body">
        <div class="section-label mb-3">Informasi Laporan</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tanggal</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                    <input type="date" name="date" class="form-control"
                        value="{{ old('date', $report ? \Carbon\Carbon::parse($report->date)->toDateString() : today()->toDateString()) }}">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Shift</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="far fa-clock"></i></span>
                    <input type="text" name="shift" class="form-control" placeholder="Mis. 1-A"
                        value="{{ old('shift', $report?->shift ?? session('shift_number') . '-' . session('shift_group')) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-2">
    <h5 class="mb-0">Informasi Produk</h5>
    <small class="text-muted"><i class="fas fa-circle text-danger me-1" style="font-size:.5rem"></i>Angka merah = di luar standar master</small>
</div>

<div id="detail-wrapper">
@foreach($details as $di => $detail)
    @if($detail && $detail->isLegacy())
        <div class="card border mb-4 bg-light">
            <div class="card-body">
                <div class="alert alert-secondary py-2">
                    <i class="fas fa-lock me-1"></i>
                    Data format lama (sebelum perombakan form), hanya bisa dilihat dan tidak ikut berubah saat disimpan.
                </div>
                @include('report_pasteurs._detail_legacy', ['detail' => $detail])
            </div>
        </div>
        @continue
    @endif
    @php
        $runs = $detail ? $detail->runs->values() : collect();
        // indeks run: dari DB + dari old() (supaya run tambahan tidak hilang saat validasi gagal)
        $ris = $runs->keys()->merge(array_keys(old("details.$di.runs", [])))->unique()->sort()->values();
        if ($ris->isEmpty()) $ris = collect([0]);
    @endphp

    <div class="card border shadow-sm mb-4 detail-item" data-autofill="{{ $detail ? 0 : 1 }}" data-next-run="{{ $ris->max() + 1 }}">
        <div class="card-header bg-light d-flex align-items-center">
            <i class="fas fa-box-open text-primary mr-2"></i>
            <strong>Produk #{{ $di + 1 }}</strong>
        </div>

        <div class="card-body">
            @if($detail)
            <input type="hidden" name="details[{{ $di }}][uuid]" value="{{ $detail->uuid }}">
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Produk</label>
                    <select name="details[{{ $di }}][product_uuid]" class="form-control select2-product">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}"
                            @selected(old("details.$di.product_uuid", $detail?->product_uuid) == $product->uuid)>
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gramasi (gr)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="details[{{ $di }}][for_packaging_gr]"
                            class="form-control inp-gramase" placeholder="Otomatis dari master produk"
                            value="{{ old("details.$di.for_packaging_gr", $detail?->for_packaging_gr) }}">
                        <span class="input-group-text">gr</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Produk <small class="text-muted">(boleh beberapa batch)</small></label>
                    <input type="text" name="details[{{ $di }}][product_code]" class="form-control"
                        placeholder="Mis. QF27801AA0"
                        value="{{ old("details.$di.product_code", $detail?->product_code) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Program</label>
                    <input type="text" name="details[{{ $di }}][program_number]" class="form-control"
                        placeholder="Mis. 12"
                        value="{{ old("details.$di.program_number", $detail?->program_number) }}">
                </div>
            </div>

            <div class="alert alert-warning py-2 master-warning d-none">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Produk ini belum punya master standar pasteurisasi. Kolom Set dikosongkan dan tidak ada penilaian merah.
            </div>

            <div class="runs-wrapper">
                @foreach($ris as $ri)
                    @include('report_pasteurs._run', ['di' => $di, 'ri' => $ri, 'run' => $runs->get($ri), 'steps' => $steps])
                @endforeach
            </div>

            <template class="run-template">
                @include('report_pasteurs._run', ['di' => $di, 'ri' => '__RI__', 'run' => null, 'steps' => $steps])
            </template>

            <button type="button" class="btn btn-outline-warning btn-sm btn-add-run">
                <i class="fas fa-redo me-1"></i> Tambah Pasteurisasi Ulang
            </button>
        </div>
    </div>
@endforeach
</div>

{{-- Catatan --}}
<div class="card border mb-4">
    <div class="card-body">
        <div class="section-label mb-2">Catatan &amp; Dokumentasi</div>
        <textarea name="problem" class="form-control" rows="3"
            placeholder="Tuliskan kendala atau catatan lain jika ada">{{ old('problem', $report?->problem) }}</textarea>
    </div>
</div>

<div class="action-bar py-3 px-3 d-flex justify-content-end gap-2 rounded-bottom">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
    <button type="submit" class="btn {{ $isAudit ? 'btn-audit' : 'btn-success' }}">
        @if($isAudit)
            <i class="fas fa-user-shield me-1"></i> Update Data Audit
        @else
            <i class="fas fa-save me-1"></i> Simpan
        @endif
    </button>
</div>
</div>