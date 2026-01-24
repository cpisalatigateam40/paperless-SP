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
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
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
                            <!-- <div class="col-md-4">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[{{ $i }}][raw_material_uuid]" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}"
                                        {{ $material->uuid == $detail->raw_material_uuid ? 'selected' : '' }}>
                                        {{ $material->material_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div> -->
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
                                <div class="d-flex flex-wrap gap-3">
                                    @php
                                    $suppliers = ['Salatiga', 'Pemalang', 'Sragen', 'Madiun', 'Banyumas'];
                                    $selectedSuppliers = explode(',', $detail->supplier ?? '');
                                    @endphp
                                    @foreach($suppliers as $supplier)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            name="details[{{ $i }}][supplier][]" value="{{ $supplier }}"
                                            id="supplier_{{ $i }}_{{ $supplier }}"
                                            {{ in_array($supplier, $selectedSuppliers) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="supplier_{{ $i }}_{{ $supplier }}">
                                            {{ $supplier }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                    value="{{ $detail->production_code }}">
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

@endsection