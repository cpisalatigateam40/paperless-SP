@extends('layouts.app')

@php
    $isEdit = isset($report);
    $oldBatches = old('batches');

    if ($oldBatches) {
        $groupedBatches = $oldBatches;
    } elseif ($isEdit) {
        $groupedBatches = [];

        foreach ($report->details as $d) {
            $key = $d->product_uuid . '|' . $d->time;

            if (!isset($groupedBatches[$key])) {
                $groupedBatches[$key] = [
                    'product_uuid'          => $d->product_uuid,
                    'time'                  => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : null,
                    'production_code'       => $d->production_code,
                    'section_uuid'          => null,
                    'machine_items'         => [],
                    'sisa_bahan_items'      => [],
                    'kondisi_ruangan_items' => [],
                ];
            }

            $rowData = [
                'score'              => $d->score,
                'notes'              => $d->notes,
                'corrective_action'  => $d->corrective_action,
            ];

            if ($d->group === 'mesin_peralatan' && $d->item_uuid) {
                $groupedBatches[$key]['machine_items'][$d->item_uuid] = $rowData;
                // ambil section dari item master (kalau tersimpan section_uuid di item-nya)
                if (!$groupedBatches[$key]['section_uuid'] && $d->item?->section_uuid) {
                    $groupedBatches[$key]['section_uuid'] = $d->item->section_uuid;
                }
            } elseif ($d->group === 'sisa_bahan') {
                $groupedBatches[$key]['sisa_bahan_items'][] = array_merge(['name' => $d->item_name], $rowData);
            } elseif ($d->group === 'kondisi_ruangan') {
                $groupedBatches[$key]['kondisi_ruangan_items'][] = array_merge(['name' => $d->item_name], $rowData);
            }
        }

        $groupedBatches = array_values($groupedBatches);
    } else {
        $groupedBatches = [
            [
                'product_uuid' => null, 'time' => null, 'section_uuid' => null,
                'machine_items' => [], 'sisa_bahan_items' => [], 'kondisi_ruangan_items' => [],
            ],
        ];
    }

    $nextBatchIndex = count($groupedBatches);
@endphp

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Pemeriksaan Kebersihan Setelah Change-Over', 'url' => route('report_changeover_cleanings.index')],
        ['label' => 'Tambah/Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4>{{ $isEdit ? 'Edit Pemeriksaan Kebersihan Setelah Change-Over' : 'Tambah Pemeriksaan Kebersihan Setelah Change-Over' }}</h4>
            <small class="text-muted">Setelah Pergantian Produk</small>
        </div>

        <div class="card-body">
            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                action="{{ $isEdit ? route('report_changeover_cleanings.update', $report->uuid) : route('report_changeover_cleanings.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', $isEdit && $report->date ? $report->date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Shift</label>
                        <input type="text" name="shift" class="form-control"
                            value="{{ old('shift', $isEdit ? $report->shift : session('shift_number') . '-' . session('shift_group')) }}"
                            required>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Pemeriksaan per Pergantian Produk</h5>
                    
                    <!-- <button type="button" id="add-batch" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Tambah Pergantian Produk
                    </button> -->
                </div>

                {{-- ===== KARTU INFO KRITERIA PENILAIAN ===== --}}
                <div class="card border-info mb-4">
                    <div class="card-header bg-info bg-opacity-10" style="margin-top: 0px !important;">
                        <strong style="color: white;"><i class="fas fa-circle-info"></i> Keterangan Pengecekan &amp; Kriteria Penilaian</strong>
                    </div>
                    <div class="card-body py-3" style="font-size: .8rem;">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <strong>Keterangan Pengecekan:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li>Sisa Bahan/Kemasan: nomor 1-8</li>
                                    <li>Mesin dan Peralatan: nomor 3-8</li>
                                    <li>Kondisi Ruangan: nomor 3-8</li>
                                </ul>
                            </div>
                            <div class="col-md-8">
                                <strong>Kriteria Penilaian:</strong>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0 mt-2 ps-3">
                                            <li>1. Bersih, tidak ada sisa bahan/kemasan sebelumnya</li>
                                            <li>3. Bebas dari kontaminasi dan bahan sebelumnya</li>
                                            <li>5. Bebas dari potensi kontaminasi allergen</li>
                                            <li>7. Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0 ps-3">
                                            <li>2. Ada sisa bahan/kemasan sebelumnya</li>
                                            <li>4. Ada kontaminasi atau sisa bahan sebelumnya</li>
                                            <li>6. Ada potensi kontaminasi allergen</li>
                                            <li>8. Tidak bersih, ada kontaminan/kotoran</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="batches-container">
                    @foreach($groupedBatches as $batchIndex => $batch)
                        @include('report_changeover_cleanings._batch', ['batchIndex' => $batchIndex, 'batch' => $batch])
                    @endforeach
                </div>

                <input type="hidden" id="next-batch-index" value="{{ $nextBatchIndex }}">

                <div class="mt-4 d-flex gap-2" style="gap: .4rem;">
                    <a href="{{ route('report_changeover_cleanings.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">
                        {{ $isEdit ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
const CRITERIA_PAIRS = @json($criteriaPairs);
const CRITERIA_LABELS = @json($criteria);
const SECTIONS = @json($sections->map(fn($s) => ['uuid' => $s->uuid, 'name' => $s->section_name]));
const PRODUCTS = @json($products->map(fn($p) => ['uuid' => $p->uuid, 'name' => $p->product_name]));
const ITEMS_BY_SECTION_URL = "{{ url('/master-checklist-items/by-section') }}";

$(document).ready(function() {
    let nextBatchIndex = parseInt($('#next-batch-index').val(), 10);

    function buildScoreSelectHtml(name) {
        let html = `<select name="${name}" class="form-control form-control-sm">`;
        html += `<option value="">-</option>`;
        CRITERIA_PAIRS.forEach(pair => {
            pair.forEach(num => {
                html += `<option value="${num}">${num} - ${CRITERIA_LABELS[num]}</option>`;
            });
        });
        html += `</select>`;
        return html;
    }

    function buildProductOptions() {
        let html = '<option value="">- Pilih Produk -</option>';
        PRODUCTS.forEach(p => html += `<option value="${p.uuid}">${p.name}</option>`);
        return html;
    }

    function buildSectionOptions() {
        let html = '<option value="">- Pilih Section -</option>';
        SECTIONS.forEach(s => html += `<option value="${s.uuid}">${s.name}</option>`);
        return html;
    }

    const SISA_BAHAN_OPTIONS = ['Sisa Bahan Baku', 'Sisa Kemasan Plastik', 'Sisa Kemasan Karton', 'Sisa Labelisasi Plastik', 'Sisa Labelisasi Karton'];

    function buildNameSelect(name, groupKey, selectedValue) {
        selectedValue = selectedValue || '';
        let options = groupKey === 'sisa_bahan_items'
            ? SISA_BAHAN_OPTIONS
            : SECTIONS.map(s => s.name);

        let html = `<select name="${name}" class="form-control">`;
        html += `<option value="">- Pilih ${groupKey === 'sisa_bahan_items' ? 'Item' : 'Section'} -</option>`;
        options.forEach(opt => {
            html += `<option value="${opt}" ${opt === selectedValue ? 'selected' : ''}>${opt}</option>`;
        });
        html += `</select>`;
        return html;
    }

    function buildManualRow(batchIndex, groupKey, rowIndex, rowData) {
        rowData = rowData || {};
        return `
            <tr>
                <td>${buildNameSelect(`batches[${batchIndex}][${groupKey}][${rowIndex}][name]`, groupKey, rowData.name)}</td>
                <td>${buildScoreSelectHtml(`batches[${batchIndex}][${groupKey}][${rowIndex}][score]`)}</td>
                <td><input type="text" name="batches[${batchIndex}][${groupKey}][${rowIndex}][notes]" class="form-control" value="${rowData.notes ?? ''}" placeholder="masukkan keterangan"></td>
                <td><input type="text" name="batches[${batchIndex}][${groupKey}][${rowIndex}][corrective_action]" class="form-control" value="${rowData.corrective_action ?? ''}" placeholder="masukkan tindakan koreksi"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-manual-row"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
    }

    function loadMachineItems(batchIndex, sectionUuid, preselected) {
        preselected = preselected || {};
        const $tbody = $(`#machine-items-body-${batchIndex}`);
        $tbody.html('<tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr>');

        if (!sectionUuid) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Pilih Section terlebih dahulu.</td></tr>');
            return;
        }

        $.get(`${ITEMS_BY_SECTION_URL}/${sectionUuid}`, function(items) {
            if (!items.length) {
                $tbody.html('<tr><td colspan="5" class="text-center text-muted">Tidak ada item untuk Section ini.</td></tr>');
                return;
            }

            let html = '';
            items.forEach(item => {
                const d = preselected[item.uuid] || {};
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${buildScoreSelectHtml(`batches[${batchIndex}][machine_items][${item.uuid}][score]`)}</td>
                        <td><input type="text" name="batches[${batchIndex}][machine_items][${item.uuid}][notes]" class="form-control" value="${d.notes ?? ''}" placeholder="masukkan keterangan"></td>
                        <td><input type="text" name="batches[${batchIndex}][machine_items][${item.uuid}][corrective_action]" class="form-control" value="${d.corrective_action ?? ''}" placeholder="masukkan tindakan koreksi"></td>
                    </tr>
                `;
            });
            $tbody.html(html);

            // set score terpilih (setelah select ter-render)
            items.forEach(item => {
                const d = preselected[item.uuid];
                if (d && d.score) {
                    $tbody.find(`select[name="batches[${batchIndex}][machine_items][${item.uuid}][score]"]`).val(d.score);
                }
            });
        });
    }

    function buildBatch(batchIndex, batch) {
        batch = batch || {};
        const $wrapper = $(`
            <div class="batch-section border rounded p-3 mb-3" data-batch-index="${batchIndex}">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Produk</label>
                        <select name="batches[${batchIndex}][product_uuid]" class="form-control" required>
                            ${buildProductOptions()}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="batches[${batchIndex}][production_code]" class="form-control" placeholder="Contoh: SOR QH08801AA0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam</label>
                        <input type="time" name="batches[${batchIndex}][time]" class="form-control" value="${batch.time ?? ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select name="batches[${batchIndex}][section_uuid]" class="form-control section-select">
                            ${buildSectionOptions()}
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-batch">
                            <i class="fas fa-trash"></i> Hapus Pergantian Produk Ini
                        </button>
                    </div>
                </div>

                <h6 class="fw-bold">Sisa Bahan dan Kemasan</h6>
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Item</th>
                            <th style="min-width:110px;">Kriteria (1-8)</th>
                            <th style="min-width:150px;">Keterangan</th>
                            <th style="min-width:150px;">Tindakan Koreksi</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody class="manual-items-body" data-group="sisa_bahan_items"></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-secondary mb-4 add-manual-row" data-group="sisa_bahan_items">
                    <i class="fas fa-plus"></i> Tambah Baris Sisa Bahan/Kemasan
                </button>

                <h6 class="fw-bold">Mesin dan Peralatan <small class="text-muted">(menyesuaikan Section yang dipilih)</small></h6>
                <table class="table table-sm table-bordered mb-4">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Item</th>
                            <th style="min-width:110px;">Kriteria (1-8)</th>
                            <th style="min-width:150px;">Keterangan</th>
                            <th style="min-width:150px;">Tindakan Koreksi</th>
                        </tr>
                    </thead>
                    <tbody id="machine-items-body-${batchIndex}">
                        <tr><td colspan="5" class="text-center text-muted">Pilih Section terlebih dahulu.</td></tr>
                    </tbody>
                </table>

                <h6 class="fw-bold">Kondisi Ruangan</h6>
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Item</th>
                            <th style="min-width:110px;">Kriteria (1-8)</th>
                            <th style="min-width:150px;">Keterangan</th>
                            <th style="min-width:150px;">Tindakan Koreksi</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody class="manual-items-body" data-group="kondisi_ruangan_items"></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-manual-row" data-group="kondisi_ruangan_items">
                    <i class="fas fa-plus"></i> Tambah Baris Kondisi Ruangan
                </button>
            </div>
        `);

        $wrapper.find(`select[name="batches[${batchIndex}][product_uuid]"]`).val(batch.product_uuid ?? '');
        $wrapper.find(`select[name="batches[${batchIndex}][section_uuid]"]`).val(batch.section_uuid ?? '');

        // isi baris manual dari data existing (edit) atau kosong 1 baris default
        ['sisa_bahan_items', 'kondisi_ruangan_items'].forEach(groupKey => {
            const $tbody = $wrapper.find(`tbody.manual-items-body[data-group="${groupKey}"]`);
            const rows = (batch[groupKey] && batch[groupKey].length) ? batch[groupKey] : [{}];
            rows.forEach((row, i) => $tbody.append(buildManualRow(batchIndex, groupKey, i, row)));
        });

        return $wrapper;
    }

    function initBatch($el, batchIndex, batch) {
        const sectionUuid = batch.section_uuid ?? $el.find('.section-select').val();
        if (sectionUuid) {
            loadMachineItems(batchIndex, sectionUuid, batch.machine_items ?? {});
        }
    }

    // Tambah pergantian produk baru
    $('#add-batch').on('click', function() {
        const $el = buildBatch(nextBatchIndex, {});
        $('#batches-container').append($el);
        nextBatchIndex++;
    });

    // Hapus satu batch
    $(document).on('click', '.remove-batch', function() {
        $(this).closest('.batch-section').remove();
    });

    // Ganti Section -> reload Mesin & Peralatan
    $(document).on('change', '.section-select', function() {
        const batchIndex = $(this).closest('.batch-section').data('batch-index');
        loadMachineItems(batchIndex, $(this).val(), {});
    });

    // Tambah baris manual (Sisa Bahan / Kondisi Ruangan)
    $(document).on('click', '.add-manual-row', function() {
        const $batchEl = $(this).closest('.batch-section');
        const batchIndex = $batchEl.data('batch-index');
        const groupKey = $(this).data('group');
        const $tbody = $batchEl.find(`tbody.manual-items-body[data-group="${groupKey}"]`);
        const rowIndex = $tbody.find('tr').length;
        $tbody.append(buildManualRow(batchIndex, groupKey, rowIndex, {}));
    });

    // Hapus baris manual
    $(document).on('click', '.remove-manual-row', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endsection