@php
    $sisaBahanRows = !empty($batch['sisa_bahan_items']) ? $batch['sisa_bahan_items'] : [[]];
    $kondisiRuanganRows = !empty($batch['kondisi_ruangan_items']) ? $batch['kondisi_ruangan_items'] : [[]];

    $machineItems = collect();
    if (!empty($batch['section_uuid'])) {
        $machineItems = \App\Models\MasterChecklistItem::where('section_uuid', $batch['section_uuid'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['uuid', 'name']);
    }
    $machineData = $batch['machine_items'] ?? [];
@endphp

<div class="batch-section border rounded p-3 mb-3" data-batch-index="{{ $batchIndex }}">
    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nama Produk</label>
            <select name="batches[{{ $batchIndex }}][product_uuid]" class="form-control" required>
                <option value="">- Pilih Produk -</option>
                @foreach($products as $product)
                <option value="{{ $product->uuid }}" @selected(($batch['product_uuid'] ?? null) == $product->uuid)>
                    {{ $product->product_name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Kode Produksi</label>
            <input type="text" name="batches[{{ $batchIndex }}][production_code]" class="form-control"
                placeholder="mis: QC12801AA0"
                value="{{ $batch['production_code'] ?? '' }}">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Jam</label>
            <input type="time" name="batches[{{ $batchIndex }}][time]" class="form-control"
                value="{{ $batch['time'] ?? '' }}">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Section</label>
            <select name="batches[{{ $batchIndex }}][section_uuid]" class="form-control section-select">
                <option value="">- Pilih Section -</option>
                @foreach($sections as $section)
                <option value="{{ $section->uuid }}" @selected(($batch['section_uuid'] ?? null) == $section->uuid)>
                    {{ $section->section_name }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- <div class="col-md-3 text-end">
            <button type="button" class="btn btn-sm btn-danger remove-batch">
                <i class="fas fa-trash"></i> Hapus Pergantian Produk Ini
            </button>
        </div> -->
    </div>

    {{-- ===== SISA BAHAN DAN KEMASAN (manual) ===== --}}
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
        <tbody class="manual-items-body" data-group="sisa_bahan_items">
            @foreach($sisaBahanRows as $rowIndex => $row)
            <tr>
                <td>
                    <select name="batches[{{ $batchIndex }}][sisa_bahan_items][{{ $rowIndex }}][name]" class="form-control">
                        <option value="">- Pilih Item -</option>
                        @foreach(['Sisa Bahan Baku', 'Sisa Kemasan Plastik', 'Sisa Kemasan Karton', 'Sisa Labelisasi Plastik', 'Sisa Labelisasi Karton'] as $opt)
                        <option value="{{ $opt }}" @selected(($row['name'] ?? null) == $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    @include('report_changeover_cleanings._score_select', [
                        'name' => "batches[{$batchIndex}][sisa_bahan_items][{$rowIndex}][score]",
                        'selected' => $row['score'] ?? null,
                        'criteria' => $criteria,
                        'criteriaPairs' => $criteriaPairs,
                    ])
                </td>
                
                <td><input type="text" name="batches[{{ $batchIndex }}][sisa_bahan_items][{{ $rowIndex }}][notes]" class="form-control" value="{{ $row['notes'] ?? '' }}" placeholder="masukkan keterangan"></td>
                <td><input type="text" name="batches[{{ $batchIndex }}][sisa_bahan_items][{{ $rowIndex }}][corrective_action]" class="form-control" value="{{ $row['corrective_action'] ?? '' }}" placeholder="masukkan tindakan koreksi"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-manual-row"><i class="fas fa-times"></i></button></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-info mb-4 add-manual-row" data-group="sisa_bahan_items">
        <i class="fas fa-plus"></i> Tambah Baris Sisa Bahan/Kemasan
    </button>

    {{-- ===== MESIN DAN PERALATAN (dari master, difilter Section) ===== --}}
    <h6 class="fw-bold">Mesin dan Peralatan <small class="text-muted">(menyesuaikan Section yang dipilih)</small></h6>
    <table class="table table-sm table-bordered mb-4">
        <thead>
            <tr>
                <th style="min-width:180px;">Item</th>
                <th style="min-width:110px;">Kriteria (3-8)</th>
                <th style="min-width:150px;">Keterangan</th>
                <th style="min-width:150px;">Tindakan Koreksi</th>
            </tr>
        </thead>
        <tbody id="machine-items-body-{{ $batchIndex }}">
            @if($machineItems->isEmpty())
                <tr><td colspan="5" class="text-center text-muted">
                    {{ !empty($batch['section_uuid']) ? 'Tidak ada item untuk Section ini.' : 'Pilih Section terlebih dahulu.' }}
                </td></tr>
            @else
                @foreach($machineItems as $item)
                @php $d = $machineData[$item->uuid] ?? []; @endphp
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>
                        @include('report_changeover_cleanings._score_select', [
                            'name' => "batches[{$batchIndex}][machine_items][{$item->uuid}][score]",
                            'selected' => $d['score'] ?? null,
                            'criteria' => $criteria,
                            'criteriaPairs' => $criteriaPairs,
                        ])
                    </td>
                    <td><input type="text" name="batches[{{ $batchIndex }}][machine_items][{{ $item->uuid }}][notes]" class="form-control" value="{{ $d['notes'] ?? '' }}" placeholder="masukkan keterangan"></td>
                    <td><input type="text" name="batches[{{ $batchIndex }}][machine_items][{{ $item->uuid }}][corrective_action]" class="form-control" value="{{ $d['corrective_action'] ?? '' }}" placeholder="masukkan tindakan koreksi"></td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- ===== KONDISI RUANGAN (manual) ===== --}}
    <h6 class="fw-bold">Kondisi Ruangan</h6>
    <table class="table table-sm table-bordered mb-0">
        <thead>
            <tr>
                <th style="min-width:180px;">Item</th>
                <th style="min-width:110px;">Kriteria (3-8)</th>
                <th style="min-width:150px;">Keterangan</th>
                <th style="min-width:150px;">Tindakan Koreksi</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody class="manual-items-body" data-group="kondisi_ruangan_items">
            @foreach($kondisiRuanganRows as $rowIndex => $row)
            <tr>
                <td>
                    <select name="batches[{{ $batchIndex }}][kondisi_ruangan_items][{{ $rowIndex }}][name]" class="form-control">
                        <option value="">- Pilih Section -</option>
                        @foreach($sections as $s)
                        <option value="{{ $s->section_name }}" @selected(($row['name'] ?? null) == $s->section_name)>{{ $s->section_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    @include('report_changeover_cleanings._score_select', [
                        'name' => "batches[{$batchIndex}][kondisi_ruangan_items][{$rowIndex}][score]",
                        'selected' => $row['score'] ?? null,
                        'criteria' => $criteria,
                        'criteriaPairs' => $criteriaPairs,
                    ])
                </td>
                <td><input type="text" name="batches[{{ $batchIndex }}][kondisi_ruangan_items][{{ $rowIndex }}][notes]" class="form-control" value="{{ $row['notes'] ?? '' }}" placeholder="masukkan keterangan"></td>
                <td><input type="text" name="batches[{{ $batchIndex }}][kondisi_ruangan_items][{{ $rowIndex }}][corrective_action]" class="form-control" value="{{ $row['corrective_action'] ?? '' }}" placeholder="masukkan tindakan koreksi"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-manual-row"><i class="fas fa-times"></i></button></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-info mt-2 add-manual-row" data-group="kondisi_ruangan_items">
        <i class="fas fa-plus"></i> Tambah Baris Kondisi Ruangan
    </button>
</div>