@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Report RM Arrivals', 'url' => route('report_rm_arrivals.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h5>Edit Verifikasi Bahan Baku dan Bahan Penunjang</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_rm_arrivals.update', $report->uuid) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" required>
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}"
                                {{ $section->uuid == $report->section_uuid ? 'selected' : '' }}>
                                {{ $section->section_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" 
                            name="date" 
                            class="form-control" 
                            value="{{ $report->date->format('Y-m-d') }}" 
                            required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <h5>Detail Pemeriksaan</h5>
                <div id="detail-container">
                    @foreach($report->details as $i => $detail)
                    <div class="detail-row p-3 border rounded bg-light" style="margin-bottom: 7rem;">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[{{ $i }}][material_uuid]"
                                        class="form-control"
                                        onchange="updateMaterialType(this)"
                                        required>

                                    <option value="">-- Pilih Bahan --</option>

                                    {{-- RAW --}}
                                    @foreach($rawMaterials as $material)
                                        <option value="{{ $material->uuid }}"
                                                data-type="raw"
                                                @selected(
                                                    $detail->material_type === 'raw'
                                                    && $detail->raw_material_uuid === $material->uuid
                                                )>
                                            {{ $material->material_name }}
                                        </option>
                                    @endforeach

                                    {{-- PREMIX --}}
                                    @foreach($premixes as $premix)
                                        <option value="{{ $premix->uuid }}"
                                                data-type="premix"
                                                @selected(
                                                    $detail->material_type === 'premix'
                                                    && $detail->material_uuid === $premix->uuid
                                                )>
                                            {{ $premix->name }} (Premix)
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden"
                                    name="details[{{ $i }}][material_type]"
                                    value="{{ $detail->material_type ?? 'raw' }}">
                            </div>


                            <div class="col-md-4">
                                <label class="form-label">Kondisi RM</label>
                                <select name="details[{{ $i }}][rm_condition]" class="form-control">
                                    <option value="Fresh (F)"
                                        {{ $detail->rm_condition == 'Fresh (F)' ? 'selected' : '' }}>Fresh (F)</option>
                                    <option value="Thawing (Th)"
                                        {{ $detail->rm_condition == 'Thawing (Th)' ? 'selected' : '' }}>Thawing (Th)
                                    </option>
                                    <option value="Frozen (Fr)"
                                        {{ $detail->rm_condition == 'Frozen (Fr)' ? 'selected' : '' }}>Frozen (Fr)
                                    </option>
                                    <option value="Dry"
                                        {{ $detail->rm_condition == 'Dry' ? 'selected' : '' }}>Dry
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Produsen</label>
                                <div class="d-flex flex-wrap gap-3 supplier-checkboxes">
                                    @php
                                        $areaName = auth()->user()->area->name ?? null;
                                        
                                        $supplierMap = [
                                            'Bandung' => [
                                                'Bandung',
                                                'Majalengka',
                                                'Salatiga',
                                                'Cikande',
                                                'Banyumas',
                                            ],
                                            'Cikande 1' => [
                                                'Cikande 1',
                                                'Cikande 3',
                                                'Bandung',
                                                'Banyumas',
                                                'Pemalang',
                                                'Sragen',
                                                'Madiun',
                                                'Majalengka',
                                                'Mojokerto',
                                                'Salatiga',
                                                'Bondowoso',
                                            ],
                                            'Medan' => [
                                                'Cikande 1',
                                                'Cikande 3',
                                                'Bandung',
                                                'Banyumas',
                                                'Pemalang',
                                                'Sragen',
                                                'Madiun',
                                                'Majalengka',
                                                'Ngoro',
                                                'Bondowoso',
                                                'Salatiga',
                                                'Medan',
                                            ],
                                            'Ngoro - Mojokerto' => [
                                                'Ngoro',
                                                'Madiun',
                                                'Bondowoso',
                                                'Majalengka',
                                            ],
                                            'Salatiga' => [
                                                'Salatiga',
                                                'Pemalang',
                                                'Sragen',
                                                'Madiun',
                                                'Banyumas',
                                            ],
                                        ];

                                        $suppliers = $supplierMap[$areaName] ?? [];
                                        $selectedSuppliers = explode(',', $detail->supplier ?? '');
                                    @endphp
                                    
                                    @forelse($suppliers as $supplier)
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                class="form-check-input supplier-checkbox"
                                                name="details[{{ $i }}][supplier][]" 
                                                value="{{ $supplier }}"
                                                id="supplier_{{ $i }}_{{ Str::slug($supplier) }}"
                                                {{ in_array($supplier, $selectedSuppliers) ? 'checked' : '' }}>
                                            <label class="form-check-label mr-3" for="supplier_{{ $i }}_{{ Str::slug($supplier) }}">
                                                {{ $supplier }}
                                            </label>
                                        </div>
                                    @empty
                                        <span class="text-muted fst-italic">
                                            Tidak ada supplier untuk area ini
                                        </span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control production-code"
                                    value="{{ $detail->production_code }}" list="productionCodes"
                                    autocomplete="off"
                                    placeholder="Contoh: QC28101CC0">
                                <datalist id="productionCodes"></datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam</label>
                                <input type="time" name="details[{{ $i }}][time]" class="form-control"
                                    value="{{ $detail->time }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Suhu (°C)</label>
                                <input type="number" step="0.1" name="details[{{ $i }}][temperature]"
                                    class="form-control" value="{{ $detail->temperature }}">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label d-block">Kemasan</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][packaging_condition]" value="✓"
                                        class="form-check-input" id="packaging_condition_ok_{{ $i }}"
                                        {{ $detail->packaging_condition == '✓' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="packaging_condition_ok_{{ $i }}">✓</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][packaging_condition]" value="x"
                                        class="form-check-input" id="packaging_condition_x_{{ $i }}"
                                        {{ $detail->packaging_condition == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="packaging_condition_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Sensory Kenampakan</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_appearance]" value="✓"
                                        class="form-check-input" id="sensory_appearance_ok_{{ $i }}"
                                        {{ $detail->sensory_appearance == '✓' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_appearance_ok_{{ $i }}">✓</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_appearance]" value="x"
                                        class="form-check-input" id="sensory_appearance_x_{{ $i }}"
                                        {{ $detail->sensory_appearance == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_appearance_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Sensory Aroma</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_aroma]" value="✓"
                                        class="form-check-input" id="sensory_aroma_ok_{{ $i }}"
                                        {{ $detail->sensory_aroma == '✓' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_aroma_ok_{{ $i }}">✓</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_aroma]" value="x"
                                        class="form-check-input" id="sensory_aroma_x_{{ $i }}"
                                        {{ $detail->sensory_aroma == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_aroma_x_{{ $i }}">x</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label d-block">Sensory Warna</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_color]" value="✓"
                                        class="form-check-input" id="sensory_color_ok_{{ $i }}"
                                        {{ $detail->sensory_color == '✓' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_color_ok_{{ $i }}">✓</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][sensory_color]" value="x"
                                        class="form-check-input" id="sensory_color_x_{{ $i }}"
                                        {{ $detail->sensory_color == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sensory_color_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Kontaminasi</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][contamination]" value="✓"
                                        class="form-check-input" id="contamination_ok_{{ $i }}"
                                        {{ $detail->contamination == '✓' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="contamination_ok_{{ $i }}">✓</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][contamination]" value="x"
                                        class="form-check-input" id="contamination_x_{{ $i }}"
                                        {{ $detail->contamination == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="contamination_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][status]" value="OK"
                                        class="form-check-input" id="status_ok_{{ $i }}"
                                        {{ $detail->status == 'OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_ok_{{ $i }}">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][status]" value="Tidak OK"
                                        class="form-check-input" id="status_tidak_ok_{{ $i }}"
                                        {{ $detail->status == 'Tidak OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_tidak_ok_{{ $i }}">Tidak OK</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 tindakan-koreksi-wrapper" style="display:none;">
                                <label class="form-label">Tindakan Koreksi</label>
                                <textarea name="details[{{ $i }}][corrective_action]" class="form-control"
                                    rows="2">{{ $detail->corrective_action }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan</label>
                                <textarea name="details[{{ $i }}][problem]" class="form-control"
                                    rows="2">{{ $detail->problem }}</textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                    @endforeach
                </div>

                <div class="form-group mt-5">
                    <label>Notes</label>
                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                        placeholder="Masukkan catatan apabila diperlukan">{{ old('notes', $report->notes) }}</textarea>
                </div>

                <!-- <button type="button" class="btn btn-sm btn-outline-primary" id="add-detail-btn">
                    + Tambah Pemeriksaan
                </button> -->

                <div class="mt-3">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateMaterialType(select) {
    const option = select.options[select.selectedIndex];
    const type = option.dataset.type || 'raw';

    const wrapper = select.closest('.col-md-4').parentElement;
    const hidden = wrapper.querySelector('input[name$="[material_type]"]');

    if (hidden) hidden.value = type;

    /**
     * OPTIONAL:
     * Kalau premix → kosongkan supplier
     */
    const supplierInput = wrapper.querySelector('select[name$="[supplier][]"]');
    if (supplierInput && type === 'premix') {
        supplierInput.value = '';
    }
}
</script>

<script>
// Mapping kode plant ke nama supplier
function getSupplierFromPlantCode(plantCode) {
    const plantMap = {
        '0': 'Eksternal',
        '1': 'Cikande 1',
        '2': 'Ngoro', // Mojokerto
        '3': 'Salatiga',
        '5': 'Bali',
        '6': 'Medan',
        '7': 'Berbek', // Sidoarjo
        '8': 'Bandung',
        '9': 'Karawang',
        'O': 'Cikande 3', // Cikande SH2
        'A': 'Banyumas',
        'B': 'Palembang',
        'C': 'Makassar',
        'D': 'Majalengka',
        'E': 'Sragen',
        'F': 'Bondowoso',
        'G': 'Pemalang',
        'H': 'Madiun'
    };

    return plantMap[plantCode] || null;
}

// Fungsi untuk mengekstrak plant code dari kode produksi
function extractPlantCode(productionCode) {
    // Format: [PREFIX] QCDDNNXZP atau QCDDNNXZP
    // Plant code ada di posisi setelah QC dan 2 digit tanggal (posisi ke-5)
    
    // Cari pola QC diikuti 2 digit, kemudian ambil 1 karakter setelahnya (angka 0-9 atau huruf A-H, O)
    const match = productionCode.match(/QC\d{2}([0-9A-HO])/i);
    
    if (match) {
        return match[1].toUpperCase();
    }
    
    return null;
}

// Fungsi untuk uncheck semua checkbox supplier di row tertentu
function uncheckAllSuppliers(row) {
    const checkboxes = row.querySelectorAll('input[type="checkbox"][name*="[supplier]"]');
    checkboxes.forEach(cb => cb.checked = false);
}

// Fungsi untuk check supplier tertentu
function checkSupplier(row, supplierName) {
    if (!supplierName) return false;
    
    const checkboxes = row.querySelectorAll('input[type="checkbox"][name*="[supplier]"]');
    let found = false;
    
    checkboxes.forEach(cb => {
        const cbValue = cb.value.toLowerCase().trim();
        const searchName = supplierName.toLowerCase().trim();
        
        // Exact match
        if (cbValue === searchName) {
            cb.checked = true;
            found = true;
            return;
        }
        
        // Special cases dengan exact match
        if (searchName === 'cikande 1' && cbValue === 'cikande') {
            cb.checked = true;
            found = true;
            return;
        }
        
        if (searchName === 'ngoro' && (cbValue === 'ngoro - mojokerto' || cbValue === 'mojokerto')) {
            cb.checked = true;
            found = true;
            return;
        }
    });
    
    return found;
}

// Event listener untuk input kode produksi
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('production-code')) return;

    const row = e.target.closest('.detail-row');
    const productionCode = e.target.value.trim();

    // Reset jika kosong
    if (!productionCode) {
        uncheckAllSuppliers(row);
        return;
    }

    // Auto-select supplier berdasarkan plant code
    const plantCode = extractPlantCode(productionCode);
    
    console.log('Production Code:', productionCode);
    console.log('Plant Code:', plantCode);
    
    if (plantCode) {
        const supplierName = getSupplierFromPlantCode(plantCode);
        console.log('Supplier Name:', supplierName);
        
        // Uncheck semua terlebih dahulu
        uncheckAllSuppliers(row);
        
        // Check supplier yang sesuai
        if (supplierName) {
            const found = checkSupplier(row, supplierName);
            
            if (found) {
                console.log(`✓ Supplier "${supplierName}" berhasil dipilih`);
            } else {
                console.log(`✗ Supplier "${supplierName}" tidak ditemukan dalam list area ini`);
            }
        }
    } else {
        // Jika plant code tidak terdeteksi, uncheck semua
        uncheckAllSuppliers(row);
        console.log('Plant code tidak terdeteksi');
    }
});
</script>

<script>
const KOREKSI_FIELDS = [
    'packaging_condition',
    'sensory_appearance',
    'sensory_aroma',
    'sensory_color',
    'contamination',
    'status'
];

function isRowNeedsKoreksi(row) {
    return KOREKSI_FIELDS.some(field => {
        const checked = row.querySelector(`input[name$="[${field}]"]:checked`);
        if (!checked) return false;
        return checked.value === 'x' || checked.value === 'Tidak OK';
    });
}

function toggleKoreksi(row) {
    const wrapper = row.querySelector('.tindakan-koreksi-wrapper');
    if (!wrapper) return;

    if (isRowNeedsKoreksi(row)) {
        wrapper.style.display = '';
    } else {
        wrapper.style.display = 'none';
        const textarea = wrapper.querySelector('textarea');
        if (textarea) textarea.value = '';
    }
}

function initKoreksiToggle(row) {
    const radios = row.querySelectorAll(
        KOREKSI_FIELDS.map(f => `input[name$="[${f}]"]`).join(',')
    );
    radios.forEach(radio => {
        radio.addEventListener('change', () => toggleKoreksi(row));
    });
    toggleKoreksi(row);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.detail-row').forEach(row => {
        try {
            initKoreksiToggle(row);
        } catch (e) {
            console.error('initKoreksiToggle gagal:', e);
        }
    });
});
</script>

@endsection