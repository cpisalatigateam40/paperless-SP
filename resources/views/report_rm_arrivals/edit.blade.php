@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Kedatangan Bahan Baku dan Bahan Penunjang</h5>
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
                    <div class="detail-row mb-3 p-3 border rounded bg-light">
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
                                <label class="form-label">Kemasan</label>
                                <select name="details[{{ $i }}][packaging_condition]" class="form-control">
                                    <option value="✓" {{ $detail->packaging_condition == '✓' ? 'selected' : '' }}>✓
                                    </option>
                                    <option value="x" {{ $detail->packaging_condition == 'x' ? 'selected' : '' }}>x
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sensory Kenampakan</label>
                                <select name="details[{{ $i }}][sensory_appearance]" class="form-control">
                                    <option value="✓" {{ $detail->sensory_appearance == '✓' ? 'selected' : '' }}>✓
                                    </option>
                                    <option value="x" {{ $detail->sensory_appearance == 'x' ? 'selected' : '' }}>x
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sensory Aroma</label>
                                <select name="details[{{ $i }}][sensory_aroma]" class="form-control">
                                    <option value="✓" {{ $detail->sensory_aroma == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $detail->sensory_aroma == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Sensory Warna</label>
                                <select name="details[{{ $i }}][sensory_color]" class="form-control">
                                    <option value="✓" {{ $detail->sensory_color == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $detail->sensory_color == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kontaminasi</label>
                                <select name="details[{{ $i }}][contamination]" class="form-control">
                                    <option value="✓" {{ $detail->contamination == '✓' ? 'selected' : '' }}>✓</option>
                                    <option value="x" {{ $detail->contamination == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Problem</label>
                                <textarea name="details[{{ $i }}][problem]" class="form-control"
                                    rows="2">{{ $detail->problem }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tindakan Koreksi</label>
                                <textarea name="details[{{ $i }}][corrective_action]" class="form-control"
                                    rows="2">{{ $detail->corrective_action }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- <button type="button" class="btn btn-sm btn-outline-primary" id="add-detail-btn">
                    + Tambah Pemeriksaan
                </button> -->

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Perbarui Laporan</button>
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

@endsection