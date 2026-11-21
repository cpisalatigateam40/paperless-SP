@php
    $isEdit = isset($report);
    $verifs = $isEdit ? $report->verifications->keyBy('test_piece') : collect();
    $dispClass = ['release' => 'mdr-ok', 'hold' => 'mdr-warn', 'reject' => 'mdr-bad'];
@endphp

<style>
    #md-reject-form{--mdr-border:#dfe3ea;--mdr-muted:#6b7280;--mdr-primary:#3b82f6;--mdr-ok:#16a34a;--mdr-bad:#dc2626;--mdr-warn:#f59e0b;--mdr-bg:#f8fafc}
    #md-reject-form *,#md-reject-form *::before,#md-reject-form *::after{box-sizing:border-box}

    /* ---------- grid sendiri (tidak tergantung Bootstrap) ---------- */
    .mdr-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:1rem}
    .mdr-grid > *{grid-column:span 12;min-width:0}
    @media (max-width:767px){
        .mdr-grid > .m6{grid-column:span 6}
        .mdr-grid > .o1{order:1}.mdr-grid > .o2{order:2}.mdr-grid > .o3{order:3}.mdr-grid > .o4{order:4}
    }
    @media (min-width:768px){
        .mdr-grid > .s2{grid-column:span 2}.mdr-grid > .s3{grid-column:span 3}
        .mdr-grid > .s4{grid-column:span 4}.mdr-grid > .s5{grid-column:span 5}
        .mdr-grid > .s6{grid-column:span 6}.mdr-grid > .s7{grid-column:span 7}
    }

    /* ---------- field ---------- */
    .mdr-label{display:block;font-size:.7rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--mdr-muted);margin:0 0 .4rem}
    .mdr-input{display:block;width:100%;height:42px;padding:.5rem .8rem;font-size:.92rem;line-height:1.4;color:#1f2937;background:#fff;border:1px solid #d5d9e0;border-radius:.55rem;transition:border-color .15s,box-shadow .15s;font-family:inherit}
    textarea.mdr-input{height:auto;min-height:92px;resize:vertical}
    .mdr-input:focus{outline:0;border-color:var(--mdr-primary);box-shadow:0 0 0 3px rgba(59,130,246,.18)}
    .mdr-input::placeholder{color:#a3aab6}
    .mdr-validated .mdr-input:invalid{border-color:var(--mdr-bad);background:#fef2f2}
    .mdr-field{position:relative}
    .mdr-field .mdr-ficon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9aa3b2;pointer-events:none;font-size:.85rem}
    .mdr-field .mdr-input{padding-left:2.4rem}
    .mdr-suffix-wrap{position:relative}
    .mdr-suffix-wrap .mdr-input{padding-right:3rem}
    .mdr-suffix{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);color:var(--mdr-muted);font-size:.8rem;pointer-events:none}

    /* select2 */
    #md-reject-form .select2-container{width:100% !important}
    #md-reject-form .select2-container--default .select2-selection--single{height:42px;border:1px solid #d5d9e0;border-radius:.55rem;display:flex;align-items:center;background:#fff}
    #md-reject-form .select2-container--default.select2-container--focus .select2-selection--single,
    #md-reject-form .select2-container--default.select2-container--open .select2-selection--single{border-color:var(--mdr-primary);box-shadow:0 0 0 3px rgba(59,130,246,.18)}
    #md-reject-form .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:1.4;padding-left:.8rem;padding-right:2rem;color:#1f2937;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    #md-reject-form .select2-container--default .select2-selection--single .select2-selection__arrow{height:100%;right:.4rem}

    /* ---------- section ---------- */
    .mdr-section{border:1px solid var(--mdr-border);border-radius:.85rem;background:#fff;margin-bottom:1.25rem;overflow:hidden}
    .mdr-section-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.8rem 1.1rem;background:var(--mdr-bg);border-bottom:1px solid var(--mdr-border)}
    .mdr-section-head h6{margin:0;font-weight:700;font-size:.95rem;color:#111827;display:flex;align-items:center}
    .mdr-section-body{padding:1.1rem}
    .mdr-step{display:inline-flex;align-items:center;justify-content:center;width:1.7rem;height:1.7rem;border-radius:50%;background:var(--mdr-primary);color:#fff;font-size:.8rem;font-weight:700;margin-right:.6rem;flex:0 0 auto}
    .mdr-title-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin:1.5rem 0 .75rem;flex-wrap:wrap}
    .mdr-title-row h6{margin:0;font-weight:700;font-size:1rem;color:#111827;display:flex;align-items:center}
    .mdr-link{background:none;border:0;color:var(--mdr-primary);font-size:.85rem;cursor:pointer;padding:.35rem .5rem;font-weight:600}
    .mdr-link:hover{text-decoration:underline}

    /* ---------- tombol pilihan (segmented) ---------- */
    .mdr-hide{position:absolute;opacity:0;width:1px;height:1px;margin:0;pointer-events:none}
    .mdr-seg{position:relative;display:inline-flex;border:1px solid #cfd5de;border-radius:.6rem;overflow:hidden;background:#fff;max-width:100%}
    .mdr-seg-btn{margin:0;padding:.55rem 1rem;font-size:.88rem;cursor:pointer;user-select:none;border-right:1px solid #e3e7ee;white-space:nowrap;color:#374151;text-align:center;line-height:1.3;transition:background .12s,color .12s}
    .mdr-seg-btn:last-of-type{border-right:0}
    .mdr-seg-btn:hover{background:#f1f5f9}
    .mdr-hide:checked + .mdr-info{background:var(--mdr-primary);color:#fff;font-weight:600}
    .mdr-hide:checked + .mdr-ok{background:var(--mdr-ok);color:#fff;font-weight:600}
    .mdr-hide:checked + .mdr-bad{background:var(--mdr-bad);color:#fff;font-weight:600}
    .mdr-hide:checked + .mdr-warn{background:var(--mdr-warn);color:#fff;font-weight:600}
    .mdr-hide:focus-visible + .mdr-seg-btn,.mdr-hide:focus-visible + .mdr-chip{outline:2px solid #93c5fd;outline-offset:-2px}
    .mdr-seg.lg .mdr-seg-btn{padding:.65rem 1.15rem}

    /* ---------- chip penyebab ---------- */
    .mdr-chips{position:relative;display:flex;flex-wrap:wrap;gap:.5rem}
    .mdr-chip{display:inline-flex;align-items:center;gap:.4rem;margin:0;padding:.45rem .9rem;border:1px solid #d5d9e0;border-radius:999px;background:#fff;font-size:.86rem;cursor:pointer;user-select:none;color:#374151;transition:all .12s;line-height:1.3}
    .mdr-chip:hover{background:#f1f5f9}
    .mdr-hide:checked + .mdr-chip{background:#e8f0fe;border-color:var(--mdr-primary);color:#1d4ed8;font-weight:600}
    .mdr-hide:checked + .mdr-chip::before{content:'\2713';font-weight:700}

    /* ---------- test piece ---------- */
    .mdr-piece{display:grid;grid-template-columns:150px auto minmax(0,1fr);gap:.9rem;align-items:center;padding:.85rem 1.1rem;border-bottom:1px solid #eef0f4}
    .mdr-piece:last-child{border-bottom:0}
    .mdr-piece-name{font-weight:700;color:#111827}

    /* ---------- kartu produk ---------- */
    .mdr-card{position:relative;border:1px solid var(--mdr-border);border-left:4px solid #cbd5e1;border-radius:.85rem;background:#fff;margin-bottom:1rem;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.05);transition:border-color .15s}
    .mdr-card[data-disp="release"]{border-left-color:var(--mdr-ok)}
    .mdr-card[data-disp="hold"]{border-left-color:var(--mdr-warn)}
    .mdr-card[data-disp="reject"]{border-left-color:var(--mdr-bad)}
    .mdr-card-head{display:flex;align-items:center;gap:.8rem;padding:.75rem 1rem;background:var(--mdr-bg);cursor:pointer;flex-wrap:wrap}
    .mdr-card-no{flex:0 0 auto;width:2rem;height:2rem;border-radius:50%;background:var(--mdr-primary);color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:.85rem}
    .mdr-card-title{flex:1 1 180px;min-width:0}
    .mdr-card-title .t{font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .mdr-card-title .s{font-size:.78rem;color:var(--mdr-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .mdr-badges{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}
    .mdr-badge{display:inline-block;padding:.2rem .65rem;border-radius:999px;font-size:.74rem;font-weight:700;color:#fff;line-height:1.4}
    .mdr-badge.b-release{background:var(--mdr-ok)}
    .mdr-badge.b-hold{background:var(--mdr-warn)}
    .mdr-badge.b-reject{background:var(--mdr-bad)}
    .mdr-badge.b-contam{background:#fff;color:var(--mdr-bad);border:1px solid var(--mdr-bad)}
    .mdr-iconbtn{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid #f3b4b4;background:#fff;color:var(--mdr-bad);border-radius:.5rem;cursor:pointer;flex:0 0 auto}
    .mdr-iconbtn:hover{background:#fef2f2}
    .mdr-caret{transition:transform .15s;color:var(--mdr-muted);flex:0 0 auto}
    .mdr-card.is-collapsed .mdr-caret{transform:rotate(-90deg)}
    .mdr-card.is-collapsed .mdr-card-body{display:none}
    .mdr-card-body{padding:1.1rem;border-top:1px solid var(--mdr-border)}
    .mdr-block{margin-bottom:1.3rem}
    .mdr-block:last-child{margin-bottom:0}
    .mdr-block-title{font-size:.76rem;font-weight:800;color:var(--mdr-primary);margin:0 0 .7rem;text-transform:uppercase;letter-spacing:.07em;display:flex;align-items:center;gap:.6rem}
    .mdr-block-title::after{content:'';flex:1;height:1px;background:#e8ecf2}

    .mdr-empty{border:2px dashed #d5d9e0;border-radius:.85rem;padding:2rem 1rem;text-align:center;color:var(--mdr-muted);margin-bottom:1rem;background:#fcfdff}

    /* ---------- bar bawah ---------- */
    .mdr-actionbar{position:sticky;bottom:0;z-index:5;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between;background:rgba(255,255,255,.96);backdrop-filter:blur(4px);border-top:1px solid var(--mdr-border);padding:.8rem 0;margin-top:1.25rem}
    .mdr-total{display:flex;gap:.5rem;flex-wrap:wrap}
    .mdr-total span{display:inline-block;background:#f1f5f9;border-radius:999px;padding:.3rem .85rem;font-size:.85rem}
    .mdr-actions{display:flex;gap:.5rem}

    /* ---------- responsif ---------- */
    @media (max-width:767px){
        .mdr-section-body{padding:.9rem}
        .mdr-piece{grid-template-columns:1fr;gap:.6rem;padding:.9rem}
        .mdr-seg{display:flex;width:100%}
        .mdr-seg-btn{flex:1;padding:.75rem .4rem;min-height:44px;display:flex;align-items:center;justify-content:center;white-space:normal}
        .mdr-card-head{padding:.65rem .75rem;gap:.55rem}
        .mdr-card-title{flex-basis:calc(100% - 7.5rem)}
        .mdr-badges{order:5;width:100%;padding-left:2.55rem}
        .mdr-card-body{padding:.9rem}
        .mdr-chip{padding:.55rem .9rem}
        .mdr-actionbar{flex-direction:column;align-items:stretch;gap:.5rem}
        .mdr-total{justify-content:center}
        .mdr-actions{display:grid;grid-template-columns:1fr 2fr}
        .mdr-actions .btn{padding:.7rem;width:100%}
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
        <div class="mdr-section-head"><h6>Informasi Umum</h6></div>
        <div class="mdr-section-body">
            <div class="mdr-grid">
                <div class="s4 m6">
                    <label class="mdr-label">Tanggal</label>
                    <div class="mdr-field">
                        <i class="fas fa-calendar-alt mdr-ficon"></i>
                        <input type="date" name="date" class="mdr-input" required
                            value="{{ old('date', $isEdit ? $report->date->format('Y-m-d') : now()->toDateString()) }}">
                    </div>
                </div>
                <div class="s4 m6">
                    <label class="mdr-label">Shift</label>
                    <div class="mdr-field">
                        <i class="fas fa-clock mdr-ficon"></i>
                        <input type="text" name="shift" class="mdr-input" required maxlength="50"
                            value="{{ old('shift', $isEdit ? $report->shift : ($defaultShift ?? 'NON-SHIFT')) }}">
                    </div>
                </div>
                <div class="s4">
                    <label class="mdr-label">Line</label>
                    <div class="mdr-field">
                        <i class="fas fa-stream mdr-ficon"></i>
                        <input type="text" name="line" class="mdr-input" required maxlength="100"
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
                    <input type="radio" class="mdr-hide" id="v{{ $i }}-det" autocomplete="off"
                        name="verifications[{{ $i }}][result]" value="detected"
                        {{ $res === 'detected' ? 'checked' : '' }} required>
                    <label class="mdr-seg-btn mdr-ok" for="v{{ $i }}-det">Terdeteksi</label>

                    <input type="radio" class="mdr-hide" id="v{{ $i }}-nodet" autocomplete="off"
                        name="verifications[{{ $i }}][result]" value="not_detected"
                        {{ $res === 'not_detected' ? 'checked' : '' }} required>
                    <label class="mdr-seg-btn mdr-bad" for="v{{ $i }}-nodet">Tidak Terdeteksi</label>
                </div>
                <input type="text" name="verifications[{{ $i }}][notes]" class="mdr-input"
                    maxlength="255" placeholder="Keterangan (opsional)" value="{{ $notes }}">
            </div>
        @endforeach
    </div>

    {{-- B. Data Produk --}}
    <div class="mdr-title-row">
        <h6><span class="mdr-step">B</span>Data Produk</h6>
        <div>
            <button type="button" class="mdr-link d-none" id="btn-toggle-all">Lipat semua</button>
            <!-- <button type="button" class="btn btn-sm btn-primary btn-add-product">
                <i class="fas fa-plus"></i> Tambah Produk
            </button> -->
        </div>
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
        <div class="mdr-actions">
            <a href="{{ route('report_md_rejects.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn-submit">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Perbarui' : 'Simpan' }}
            </button>
        </div>
    </div>
</form>

{{-- Template satu produk (dipakai oleh _script) --}}
<template id="detail-template">
    <div class="mdr-card detail-row" data-i="__I__" data-disp="">
        <div class="mdr-card-head">
            <div class="mdr-card-no row-no"></div>
            <div class="mdr-card-title">
                <div class="t" data-s="title">Produk belum dipilih</div>
                <div class="s" data-s="sub">-</div>
            </div>
            <button type="button" class="mdr-iconbtn btn-remove-row" title="Hapus produk">
                <i class="fas fa-trash"></i>
            </button>
            <i class="fas fa-chevron-down mdr-caret"></i>
            <div class="mdr-badges">
                <span class="mdr-badge d-none" data-s="disp"></span>
                <span class="mdr-badge b-contam d-none" data-s="contam">Kontaminasi</span>
            </div>
        </div>

        <div class="mdr-card-body">
            <input type="hidden" name="details[__I__][uuid]">

            <div class="mdr-block">
                <div class="mdr-block-title">Identitas Produk</div>
                <div class="mdr-grid">
                    <div class="s2 m6 o2">
                        <label class="mdr-label">Jam</label>
                        <input type="time" name="details[__I__][time]" data-f="time" class="mdr-input" required>
                    </div>
                    <div class="s5 o1">
                        <label class="mdr-label">Nama Produk</label>
                        <select name="details[__I__][product_uuid]" data-f="product" class="mdr-input" required>
                            <option value=""></option>
                            @foreach($products as $p)
                                <option value="{{ $p->uuid }}">{{ $p->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="s3 o4">
                        <label class="mdr-label">Kode Produksi</label>
                        <input type="text" name="details[__I__][production_code]" data-f="code"
                            class="mdr-input" maxlength="100" required>
                    </div>
                    <div class="s2 m6 o3">
                        <label class="mdr-label">Jumlah Reject</label>
                        <div class="mdr-suffix-wrap">
                            <input type="number" min="0" step="1" inputmode="numeric"
                                name="details[__I__][reject_qty]" data-f="qty" class="mdr-input" required>
                            <span class="mdr-suffix">pcs</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Hasil Pengecekan</div>
                <div class="mdr-grid">
                    <div class="s6">
                        <label class="mdr-label">Hasil Pengecekan Ulang</label>
                        <div class="mdr-seg">
                            <input type="radio" class="mdr-hide" id="d__I__-rc-det" autocomplete="off"
                                name="details[__I__][recheck_result]" value="detected" required>
                            <label class="mdr-seg-btn mdr-info" for="d__I__-rc-det">Terdeteksi</label>
                            <input type="radio" class="mdr-hide" id="d__I__-rc-nodet" autocomplete="off"
                                name="details[__I__][recheck_result]" value="not_detected" required>
                            <label class="mdr-seg-btn mdr-info" for="d__I__-rc-nodet">Tidak Terdeteksi</label>
                        </div>
                    </div>
                    <div class="s6">
                        <label class="mdr-label">Kontaminasi Ditemukan</label>
                        <div class="mdr-seg">
                            <input type="radio" class="mdr-hide" id="d__I__-ct-yes" autocomplete="off"
                                name="details[__I__][contamination_found]" value="1" required>
                            <label class="mdr-seg-btn mdr-bad" for="d__I__-ct-yes">Ada</label>
                            <input type="radio" class="mdr-hide" id="d__I__-ct-no" autocomplete="off"
                                name="details[__I__][contamination_found]" value="0" required>
                            <label class="mdr-seg-btn mdr-ok" for="d__I__-ct-no">Tidak</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Dugaan Penyebab</div>
                <div class="mdr-chips">
                    @foreach($causes as $key => $label)
                        <input type="checkbox" class="mdr-hide {{ $key === 'other' ? 'cause-other-cb' : '' }}"
                            id="d__I__-cause-{{ $key }}" autocomplete="off"
                            name="details[__I__][suspected_causes][]" value="{{ $key }}">
                        <label class="mdr-chip" for="d__I__-cause-{{ $key }}">{{ $label }}</label>
                    @endforeach
                </div>
                <input type="text" name="details[__I__][suspected_cause_other]"
                    class="mdr-input cause-other-input d-none" style="margin-top:.7rem" maxlength="255"
                    placeholder="Sebutkan penyebab lainnya...">
            </div>

            <div class="mdr-block">
                <div class="mdr-block-title">Tindakan &amp; Disposisi</div>
                <div class="mdr-grid">
                    <div class="s7">
                        <label class="mdr-label">Tindakan Perbaikan / Korektif</label>
                        <textarea name="details[__I__][corrective_action]" rows="3" class="mdr-input"
                            placeholder="Tuliskan tindakan yang dilakukan..."></textarea>
                    </div>
                    <div class="s5">
                        <label class="mdr-label">Disposisi Produk</label>
                        <div class="mdr-seg lg">
                            @foreach($dispositions as $key => $label)
                                <input type="radio" class="mdr-hide" id="d__I__-disp-{{ $key }}" autocomplete="off"
                                    name="details[__I__][disposition]" value="{{ $key }}"
                                    data-label="{{ $label }}" required>
                                <label class="mdr-seg-btn {{ $dispClass[$key] ?? 'mdr-info' }}" for="d__I__-disp-{{ $key }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>