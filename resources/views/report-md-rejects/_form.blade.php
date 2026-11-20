@php
    $isEdit = isset($report);
    $verifs = $isEdit ? $report->verifications->keyBy('test_piece') : collect();
    $dispClass = ['release' => 'mdr-ok', 'hold' => 'mdr-warn', 'reject' => 'mdr-bad'];
@endphp

<style>
    .mdr-label{display:block;font-size:.72rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#6b7280;margin:0 0 .35rem}
    .mdr-section{border:1px solid #e5e7eb;border-radius:.75rem;background:#fff;margin-bottom:1.25rem;overflow:hidden}
    .mdr-section-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.75rem 1rem;background:#f8fafc;border-bottom:1px solid #e5e7eb}
    .mdr-section-head h6{margin:0;font-weight:700;font-size:.95rem}
    .mdr-step{display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;border-radius:50%;background:#3b82f6;color:#fff;font-size:.8rem;font-weight:700;margin-right:.5rem}
    .mdr-section-body{padding:1rem}

    /* segmented radio */
    .mdr-seg{display:inline-flex;border:1px solid #d5d9e0;border-radius:.5rem;overflow:hidden;background:#fff;max-width:100%}
    .mdr-seg-btn{margin:0;padding:.45rem .9rem;font-size:.85rem;cursor:pointer;user-select:none;border-right:1px solid #d5d9e0;white-space:nowrap;color:#374151;transition:background .12s,color .12s}
    .mdr-seg-btn:last-of-type{border-right:0}
    .mdr-seg-btn:hover{background:#f3f4f6}
    .btn-check:checked + .mdr-info{background:#3b82f6;color:#fff}
    .btn-check:checked + .mdr-ok{background:#16a34a;color:#fff}
    .btn-check:checked + .mdr-bad{background:#dc2626;color:#fff}
    .btn-check:checked + .mdr-warn{background:#f59e0b;color:#fff}
    .btn-check:focus-visible + .mdr-seg-btn,.btn-check:focus-visible + .mdr-chip{outline:2px solid #93c5fd;outline-offset:1px}
    .mdr-seg.mdr-lg .mdr-seg-btn{padding:.55rem 1.1rem;font-weight:600}

    /* chip penyebab */
    .mdr-chip{display:inline-flex;align-items:center;gap:.35rem;margin:0 .4rem .5rem 0;padding:.35rem .8rem;border:1px solid #d5d9e0;border-radius:999px;background:#fff;font-size:.85rem;cursor:pointer;user-select:none;transition:all .12s}
    .mdr-chip:hover{background:#f3f4f6}
    .btn-check:checked + .mdr-chip{background:#e8f0fe;border-color:#3b82f6;color:#1d4ed8;font-weight:600}
    .btn-check:checked + .mdr-chip::before{content:'\2713'}

    /* test piece */
    .mdr-piece{display:grid;grid-template-columns:160px auto 1fr;gap:.75rem;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #eef0f3}
    .mdr-piece:last-child{border-bottom:0}
    .mdr-piece-name{font-weight:600}

    /* kartu produk */
    .mdr-card{border:1px solid #e5e7eb;border-radius:.75rem;background:#fff;margin-bottom:1rem;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .mdr-card-head{display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;background:#f8fafc;cursor:pointer}
    .mdr-card-no{flex:0 0 auto;width:2rem;height:2rem;border-radius:50%;background:#3b82f6;color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:.85rem}
    .mdr-card-title{flex:1 1 auto;min-width:0}
    .mdr-card-title .t{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .mdr-card-title .s{font-size:.78rem;color:#6b7280}
    .mdr-caret{transition:transform .15s;color:#6b7280}
    .mdr-card.is-collapsed .mdr-caret{transform:rotate(-90deg)}
    .mdr-card.is-collapsed .mdr-card-body{display:none}
    .mdr-card-body{padding:1rem;border-top:1px solid #e5e7eb}
    .mdr-block{margin-bottom:1rem}
    .mdr-block-title{font-size:.8rem;font-weight:700;color:#3b82f6;margin-bottom:.6rem;text-transform:uppercase;letter-spacing:.05em}

    .mdr-empty{border:2px dashed #d5d9e0;border-radius:.75rem;padding:2rem 1rem;text-align:center;color:#6b7280;margin-bottom:1rem}

    .mdr-actionbar{position:sticky;bottom:0;z-index:5;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between;background:#fff;border-top:1px solid #e5e7eb;padding:.75rem 0;margin-top:1rem}
    .mdr-total span{display:inline-block;background:#f1f5f9;border-radius:999px;padding:.25rem .75rem;font-size:.85rem;margin-right:.4rem}

    /* select2 menyesuaikan form-control Bootstrap 5 */
    #md-reject-form .select2-container{width:100% !important}
    #md-reject-form .select2-container--default .select2-selection--single{height:calc(1.5em + .75rem + 2px);border:1px solid #dee2e6;border-radius:.375rem;display:flex;align-items:center}
    #md-reject-form .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:1.5;padding-left:.75rem;color:#212529}
    #md-reject-form .select2-container--default .select2-selection--single .select2-selection__arrow{height:100%}

    @media (max-width: 768px){
        .mdr-piece{grid-template-columns:1fr}
        .mdr-seg{display:flex;width:100%}
        .mdr-seg-btn{flex:1;text-align:center;padding-inline:.4rem}
    }
</style>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" id="md-reject-form" autocomplete="off">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Informasi umum --}}
    <div class="mdr-section">
        <div class="mdr-section-head">
            <h6>Informasi Umum</h6>
        </div>
        <div class="mdr-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="mdr-label">Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" name="date" class="form-control" required
                            value="{{ old('date', $isEdit ? $report->date->format('Y-m-d') : now()->toDateString()) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="mdr-label">Shift</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="text" name="shift" class="form-control" required maxlength="50"
                            value="{{ old('shift', $isEdit ? $report->shift : ($defaultShift ?? 'NON-SHIFT')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="mdr-label">Line</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-stream"></i></span>
                        <input type="text" name="line" class="form-control" required maxlength="100"
                            placeholder="Contoh: Line 1"
                            value="{{ old('line', $isEdit ? $report->line : '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- A. Verifikasi Alat --}}
    <div class="mdr-section">
        <div class="mdr-section-head">
            <h6><span class="mdr-step">A</span>Verifikasi Alat (Test Piece)</h6>
        </div>
        @foreach($testPieces as $i => $piece)
            @php
                $res   = old("verifications.$i.result", optional($verifs->get($piece))->result);
                $notes = old("verifications.$i.notes", optional($verifs->get($piece))->notes);
            @endphp
            <div class="mdr-piece">
                <div class="mdr-piece-name">
                    {{ $piece }}
                    <input type="hidden" name="verifications[{{ $i }}][test_piece]" value="{{ $piece }}">
                </div>
                <div class="mdr-seg">
                    <input type="radio" class="btn-check" id="v{{ $i }}-det" autocomplete="off"
                        name="verifications[{{ $i }}][result]" value="detected"
                        {{ $res === 'detected' ? 'checked' : '' }} required>
                    <label class="mdr-seg-btn mdr-ok" for="v{{ $i }}-det">Terdeteksi</label>

                    <input type="radio" class="btn-check" id="v{{ $i }}-nodet" autocomplete="off"
                        name="verifications[{{ $i }}][result]" value="not_detected"
                        {{ $res === 'not_detected' ? 'checked' : '' }} required>
                    <label class="mdr-seg-btn mdr-bad" for="v{{ $i }}-nodet">Tidak Terdeteksi</label>
                </div>
                <input type="text" name="verifications[{{ $i }}][notes]" class="form-control"
                    maxlength="255" placeholder="Keterangan (opsional)" value="{{ $notes }}">
            </div>
        @endforeach
    </div>

    {{-- B. Data Produk --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0"><span class="mdr-step">B</span>Data Produk</h6>
        <button type="button" class="btn btn-sm btn-primary btn-add-product d-none">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <div id="detail-rows"></div>

    <div id="empty-hint" class="mdr-empty d-none">
        <div class="mb-2"><i class="fas fa-box-open fa-2x"></i></div>
        <div class="mb-2">Belum ada produk reject.</div>
        <button type="button" class="btn btn-sm btn-outline-primary btn-add-product">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <div id="add-bottom" class="text-left mb-2 d-none">
        <button type="button" class="btn btn-outline-primary btn-add-product">
            <i class="fas fa-plus"></i> Tambah Produk Lagi
        </button>
    </div>

    <div class="mdr-actionbar">
        <div class="mdr-total">
            <span>Produk: <strong id="total-products">0</strong></span>
            <span>Total reject: <strong id="total-reject">0</strong> pcs/pack</span>
        </div>
        <div class="d-flex" style="gap:.5rem;">
            <a href="{{ route('report-md-rejects.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success" id="btn-submit">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Perbarui' : 'Simpan' }}
            </button>
        </div>
    </div>
</form>

{{-- Template satu produk (dipakai oleh _script) --}}
<template id="detail-template">
    <div class="mdr-card detail-row" data-i="__I__">
        <div class="mdr-card-head">
            <div class="mdr-card-no row-no"></div>
            <div class="mdr-card-title">
                <div class="t" data-s="title">Produk belum dipilih</div>
                <div class="s" data-s="sub">-</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus produk">
                <i class="fas fa-trash"></i>
            </button>
            <i class="fas fa-chevron-down mdr-caret"></i>
        </div>

        <div class="mdr-card-body">
            <input type="hidden" name="details[__I__][uuid]">

            <div class="mdr-block">
                <div class="mdr-block-title">Identitas Produk</div>
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <label class="mdr-label">Jam</label>
                        <input type="time" name="details[__I__][time]" data-f="time" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="mdr-label">Nama Produk</label>
                        <select name="details[__I__][product_uuid]" data-f="product" class="form-select" required>
                            <option value=""></option>
                            @foreach($products as $p)
                                <option value="{{ $p->uuid }}">{{ $p->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="mdr-label">Kode Produksi</label>
                        <input type="text" name="details[__I__][production_code]" data-f="code"
                            class="form-control" maxlength="100" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="mdr-label">Jumlah Reject</label>
                        <div class="input-group">
                            <input type="number" min="0" step="1" name="details[__I__][reject_qty]" data-f="qty"
                                class="form-control" required>
                            <span class="input-group-text">pcs</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Hasil Pengecekan</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="mdr-label">Hasil Pengecekan Ulang</label>
                        <div class="mdr-seg">
                            <input type="radio" class="btn-check" id="d__I__-rc-det" autocomplete="off"
                                name="details[__I__][recheck_result]" value="detected" required>
                            <label class="mdr-seg-btn mdr-info" for="d__I__-rc-det">Terdeteksi</label>
                            <input type="radio" class="btn-check" id="d__I__-rc-nodet" autocomplete="off"
                                name="details[__I__][recheck_result]" value="not_detected" required>
                            <label class="mdr-seg-btn mdr-info" for="d__I__-rc-nodet">Tidak Terdeteksi</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="mdr-label">Kontaminasi Ditemukan</label>
                        <div class="mdr-seg">
                            <input type="radio" class="btn-check" id="d__I__-ct-yes" autocomplete="off"
                                name="details[__I__][contamination_found]" value="1" required>
                            <label class="mdr-seg-btn mdr-bad" for="d__I__-ct-yes">Ada</label>
                            <input type="radio" class="btn-check" id="d__I__-ct-no" autocomplete="off"
                                name="details[__I__][contamination_found]" value="0" required>
                            <label class="mdr-seg-btn mdr-ok" for="d__I__-ct-no">Tidak</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Dugaan Penyebab</div>
                <div>
                    @foreach($causes as $key => $label)
                        <input type="checkbox" class="btn-check {{ $key === 'other' ? 'cause-other-cb' : '' }}"
                            id="d__I__-cause-{{ $key }}" autocomplete="off"
                            name="details[__I__][suspected_causes][]" value="{{ $key }}">
                        <label class="mdr-chip" for="d__I__-cause-{{ $key }}">{{ $label }}</label>
                    @endforeach
                </div>
                <input type="text" name="details[__I__][suspected_cause_other]"
                    class="form-control mt-1 cause-other-input d-none" maxlength="255"
                    placeholder="Sebutkan penyebab lainnya...">
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Tindakan &amp; Disposisi</div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="mdr-label">Tindakan Perbaikan / Korektif</label>
                        <textarea name="details[__I__][corrective_action]" rows="3" class="form-control"
                            placeholder="Tuliskan tindakan yang dilakukan..."></textarea>
                    </div>
                    <div class="col-md-5">
                        <label class="mdr-label">Disposisi Produk</label>
                        <div class="mdr-seg mdr-lg">
                            @foreach($dispositions as $key => $label)
                                <input type="radio" class="btn-check" id="d__I__-disp-{{ $key }}" autocomplete="off"
                                    name="details[__I__][disposition]" value="{{ $key }}" required>
                                <label class="mdr-seg-btn {{ $dispClass[$key] ?? 'mdr-info' }}" for="d__I__-disp-{{ $key }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>