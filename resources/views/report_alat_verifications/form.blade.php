@extends('layouts.app')

@php
    $isEdit = isset($report);
@endphp

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Alat Ukur', 'url' => route('report-alat-verifications.index')],
        ['label' => 'Tambah/Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>{{ $isEdit ? 'Edit' : 'Tambah' }} Verifikasi Alat Ukur</h5>
        </div>

        <div class="card-body">
            <form action="{{ $isEdit ? route('report-alat-verifications.update', $report->uuid) : route('report-alat-verifications.store') }}"
                method="POST">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- Data Header --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date">Tanggal</label>
                        <input type="date" name="date"
                            value="{{ old('date', $isEdit ? $report->date->format('Y-m-d') : \Carbon\Carbon::today()->toDateString()) }}"
                            class="form-control @error('date') is-invalid @enderror" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="shift">Shift</label>
                        <input type="text" name="shift" class="form-control @error('shift') is-invalid @enderror"
                            value="{{ old('shift', $isEdit ? $report->shift : session('shift_number') . '-' . session('shift_group')) }}"
                            required>
                        @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="global-check-time">Jam Pemeriksaan</label>
                        <input type="time" id="global-check-time" class="form-control" value="08:00">
                        <small class="text-muted">Otomatis diisikan ke semua baris, bisa diubah per baris.</small>
                    </div>
                </div>

                @error('items') <div class="alert alert-danger">{{ $message }}</div> @enderror

                {{-- List Alat --}}
                <table class="table table-bordered align-middle" id="alat-table">
                    <thead>
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:25%">Jenis & Kode Alat</th>
                            <th style="width:20%">Titik Ukur</th>
                            <th style="width:15%">Nilai Baca (kg/°C)</th>
                            <th style="width:15%">Jam Pemeriksaan</th>
                            <th style="width:15%">Catatan</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="detail-body"></tbody>
                </table>

                <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="add-row">+ Tambah Baris</button>

                <div class="mt-4">
                    <a href="{{ route('report-alat-verifications.index') }}" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@php
    $scaleOptions = $scales->map(fn($s) => [
        'uuid' => $s->uuid,
        'label' => $s->type . ' - ' . $s->code,
    ]);

    $thermometerOptions = $thermometers->map(fn($t) => [
        'uuid' => $t->uuid,
        'label' => $t->type . ' - ' . $t->code,
    ]);

    $existingDetailsArray = $isEdit
        ? $report->details->map(fn($d) => [
            'alat_type' => $d->alat_type,
            'alat_uuid' => $d->alat_uuid,
            'titik_ukur' => $d->titik_ukur,
            'nilai_baca' => $d->nilai_baca,
            'check_time' => $d->check_time,
            'notes' => $d->notes,
        ])
        : collect();
@endphp
<script>
const scales = @json($scaleOptions);
const thermometers = @json($thermometerOptions);
const existingDetails = @json($existingDetailsArray);

const titikUkurOptions = {
    scale: [
        '100 Gr',
        '200 Gr',
        '500 Gr',
        '1000 Gr',
        '2000 Gr',
        '5000 Gr',
        '10000 Gr',
        '15000 Gr',
        '20000 Gr',
        '25000 Gr',
        '50000 Gr',
    ],
    thermometer: [
        '-18 °C',  // suhu freezer
        '0 °C',    // titik beku
        '4 °C',    // suhu chiller
        '10 °C',
        '37 °C',   // suhu tubuh
        '60 °C',
        '75 °C',   // suhu masak minimum
        '82 °C',
        '100 °C',  // titik didih
    ],
};

let rowCount = 0;

function buildAlatOptions(selectedUuid = null) {
    let html = '<option value="">-- Pilih Alat --</option>';
    html += '<optgroup label="Timbangan">';
    scales.forEach(s => {
        const sel = s.uuid === selectedUuid ? 'selected' : '';
        html += `<option value="${s.uuid}" data-type="scale" ${sel}>${s.label}</option>`;
    });
    html += '</optgroup><optgroup label="Thermometer">';
    thermometers.forEach(t => {
        const sel = t.uuid === selectedUuid ? 'selected' : '';
        html += `<option value="${t.uuid}" data-type="thermometer" ${sel}>${t.label}</option>`;
    });
    html += '</optgroup>';
    return html;
}

function buildTitikUkurOptions(alatType, selectedValue = null) {
    const options = titikUkurOptions[alatType] || [];
    let html = '<option value="">-- Pilih Titik Ukur --</option>';
    let matched = false;
    options.forEach(opt => {
        const sel = opt === selectedValue ? 'selected' : '';
        if (sel) matched = true;
        html += `<option value="${opt}" ${sel}>${opt}</option>`;
    });
    // titik ukur lama yang tidak ada di list standar (data legacy/custom) tetap ditampilkan
    if (selectedValue && !matched) {
        html += `<option value="${selectedValue}" selected>${selectedValue}</option>`;
    }
    return html;
}

function addRow(data = null) {
    const tbody = document.getElementById('detail-body');
    const index = rowCount;
    const globalTime = document.getElementById('global-check-time').value;

    const alatType = data?.alat_type ?? '';
    const checkTime = data?.check_time ?? globalTime;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center row-number">${index + 1}</td>
        <td>
            <select name="items[${index}][alat_uuid]" class="form-select form-control alat-select" required>
                ${buildAlatOptions(data?.alat_uuid ?? null)}
            </select>
            <input type="hidden" name="items[${index}][alat_type]" class="alat-type-input" value="${alatType}">
        </td>
        <td>
            <select name="items[${index}][titik_ukur]" class="form-select form-control titik-ukur-select" required ${alatType ? '' : 'disabled'}>
                ${buildTitikUkurOptions(alatType, data?.titik_ukur ?? null)}
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${index}][nilai_baca]" class="form-control"
                value="${data?.nilai_baca ?? ''}" required placeholder="mis: 12.5">
        </td>
        <td>
            <input type="time" name="items[${index}][check_time]" class="form-control check-time-input"
                value="${checkTime}">
        </td>
        <td>
            <input type="text" name="items[${index}][notes]" class="form-control" value="${data?.notes ?? ''}" placeholder="catatan (opsional)">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button>
        </td>
    `;

    tbody.appendChild(row);
    rowCount++;
    renumberRows();
}

function renumberRows() {
    document.querySelectorAll('#detail-body tr').forEach((row, i) => {
        row.querySelector('.row-number').textContent = i + 1;
    });
}

// Saat alat dipilih: set alat_type tersembunyi + refresh opsi titik ukur
document.getElementById('detail-body').addEventListener('change', function (e) {
    if (e.target.classList.contains('alat-select')) {
        const row = e.target.closest('tr');
        const selectedOption = e.target.selectedOptions[0];
        const alatType = selectedOption?.dataset.type ?? '';

        row.querySelector('.alat-type-input').value = alatType;

        const titikUkurSelect = row.querySelector('.titik-ukur-select');
        titikUkurSelect.innerHTML = buildTitikUkurOptions(alatType);
        titikUkurSelect.disabled = !alatType;
    }
});

// Hapus baris
document.getElementById('detail-body').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        renumberRows();
    }
});

// Tambah baris kosong
document.getElementById('add-row').addEventListener('click', () => addRow());

// Sync jam pemeriksaan global ke semua baris (baris yang sudah ada nilainya tidak ditimpa otomatis,
// hanya baris baru ikut nilai global saat ditambahkan; user bisa override per baris)
document.getElementById('global-check-time').addEventListener('change', function () {
    document.querySelectorAll('.check-time-input').forEach(input => {
        if (!input.dataset.touched) input.value = this.value;
    });
});
document.getElementById('detail-body').addEventListener('input', function (e) {
    if (e.target.classList.contains('check-time-input')) {
        e.target.dataset.touched = 'true';
    }
});

// Render baris awal
if (existingDetails.length > 0) {
    existingDetails.forEach(d => addRow(d));
} else {
    addRow();
}
</script>
@endsection