@extends('layouts.app')

@section('content')
@php
$conditionOptions_1_8 = [
1 => '1 - Sesuai Spesifikasi',
2 => '2 - Tidak Sesuai Spesifikasi',
3 => '3 - Bebas dari kontaminan dan bahan sebelumnya',
4 => '4 - Ada kontaminan atau sisa bahan sebelumnya',
5 => '5 - Bebas dari potensi kontaminasi allergen',
6 => '6 - Ada potensi kontaminasi allergen',
7 => '7 - Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang',
8 => '8 - Tidak bersih, ada kontaminan/kotoran, tercium bau menyimpang',
];

// dropdown per bagian
$conditionOptions_1_6 = array_slice($conditionOptions_1_8, 0, 6, true); // bahan baku & penunjang
$conditionOptions_1_2 = array_slice($conditionOptions_1_8, 0, 2, true); // kemasan
$conditionOptions_3_8 = array_slice($conditionOptions_1_8, 2, 6, true); // mesin & ruangan
@endphp


<div class="container-fluid">
    <form action="{{ route('report_pre_operations.store') }}" method="POST">
        @csrf

        {{-- HEADER --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Form Pemeriksaan Pra Operasi</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-3 mb-2">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Produk</label>
                    <select name="product_uuid" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name ?? $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" required>
                </div>
            </div>
        </div>

        {{-- BAHAN BAKU & PENUNJANG --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Bahan Baku dan Penunjang</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Jenis</th>
                            <th class="align-middle text-center">Nama Bahan</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="5"> {{-- total kolom --}}
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                    </thead>
                    <tbody id="material-rows">
                    </tbody>
                </table>

                <button type="button" class="btn btn-sm btn-outline-primary mt-3" onclick="addMaterialRow()">+ Tambah
                    Baris</button>

            </div>
        </div>

        {{-- KEMASAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Kemasan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Item</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($packagingItems as $i => $item)
                        <tr>
                            <td class="align-middle">
                                <input type="hidden" name="packagings[{{ $i }}][item]" value="{{ $item }}">
                                {{ $item }}
                            </td>
                            <td>
                                <select name="packagings[{{ $i }}][condition]" class="form-control condition-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_1_2 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="packagings[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="packagings[{{ $i }}][verification]"
                                    class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MESIN & PERALATAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Mesin dan Peralatan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Nama Peralatan</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipments as $i => $equipment)
                        <tr>
                            <td>
                                <input type="hidden" name="equipments[{{ $i }}][equipment_uuid]"
                                    value="{{ $equipment->uuid }}">
                                {{ $equipment->name }}
                            </td>
                            <td>
                                <select name="equipments[{{ $i }}][condition]" class="form-control condition-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="equipments[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="equipments[{{ $i }}][verification]"
                                    class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        {{-- KONDISI RUANGAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Kondisi Ruangan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Nama Ruangan</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $i => $section)
                        <tr>
                            <td>
                                <input type="hidden" name="rooms[{{ $i }}][section_uuid]" value="{{ $section->uuid }}">
                                {{ $section->section_name ?? $section->name }}
                            </td>
                            <td>
                                <select name="rooms[{{ $i }}][condition]" class="form-control condition-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="rooms[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="rooms[{{ $i }}][verification]" class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            {{-- SUBMIT --}}
            <div class="card-body">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </div>


    </form>
</div>
@endsection

@section('script')
<script>
let materialIndex = 0;
const conditionOptions = @json($conditionOptions_1_6);

function addMaterialRow() {
    const tbody = document.getElementById('material-rows');
    const row = document.createElement('tr');

    let conditionSelect = `<select name="materials[${materialIndex}][condition]" class="form-control condition-select">
        <option value="">-- Pilih --</option>`;
    for (const key in conditionOptions) {
        conditionSelect += `<option value="${key}">${conditionOptions[key]}</option>`;
    }
    conditionSelect += `</select>`;

    row.innerHTML = `
        <td>
            <select name="materials[${materialIndex}][type]" class="form-control">
                <option value="raw_material">Bahan Baku</option>
                <option value="supporting_material">Bahan Penunjang</option>
            </select>
        </td>
        <td><input type="text" name="materials[${materialIndex}][item]" class="form-control"></td>
        <td>${conditionSelect}</td>
        <td><input type="text" name="materials[${materialIndex}][corrective_action]" class="form-control corrective-action" readonly></td>
        <td>
            <select name="materials[${materialIndex}][verification]" class="form-control verification-select">
                <option value="">--Pilih--</option>
                <option value="0">Tidak OK</option>
                <option value="1">OK</option>
            </select>
        </td>
    `;
    tbody.appendChild(row);

    // Tambahkan followup row
    const followupRow = document.createElement('tr');
    followupRow.classList.add('followup-row');
    followupRow.innerHTML = `<td colspan="5"><div class="followup-wrapper"></div></td>`;
    tbody.appendChild(followupRow);

    initEvents(row);

    materialIndex++;
}

function initEvents(row) {
    const conditionSelect = row.querySelector('.condition-select');
    const corrective = row.querySelector('.corrective-action');
    const verification = row.querySelector('.verification-select');

    conditionSelect.addEventListener('change', function() {
        const val = parseInt(this.value);
        clearFollowups(row);

        corrective.value = '';
        corrective.setAttribute('readonly', true);
        verification.value = '';

        if ([1, 3, 5, 7].includes(val)) {
            verification.value = '1';
        } else if ([2, 4, 6, 8].includes(val)) {
            corrective.removeAttribute('readonly');
            verification.value = '0';
            addFollowupField(row);
        } else if ([1, 2].includes(val)) { // khusus kemasan
            if (val === 1) {
                verification.value = '1';
            } else if (val === 2) {
                corrective.removeAttribute('readonly');
                verification.value = '0';
                addFollowupField(row);
            }
        }
    });

    verification.addEventListener('change', function() {
        clearFollowups(row);
        if (this.value === '0') addFollowupField(row);
    });
}

function addFollowupField(row) {
    const wrapper = getFollowupWrapper(row);
    const baseName = row.querySelector('.verification-select').name.replace('[verification]', '');
    const idx = wrapper.querySelectorAll('.followup-group').length;

    const html = `
        <div class="followup-group border rounded p-2 mb-2">
            <label class="small mb-1">Koreksi Lanjutan #${idx+1}</label>
            <input type="text" name="${baseName}[followups][${idx}][notes]" class="form-control mb-1" placeholder="Catatan">
            <input type="text" name="${baseName}[followups][${idx}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
            <select name="${baseName}[followups][${idx}][verification]" class="form-control followup-verification">
                <option value="">--Pilih--</option>
                <option value="0">Tidak OK</option>
                <option value="1">OK</option>
            </select>
        </div>`;
    wrapper.insertAdjacentHTML('beforeend', html);

    const newSelect = wrapper.querySelectorAll('.followup-verification')[idx];
    newSelect.addEventListener('change', function() {
        const followups = wrapper.querySelectorAll('.followup-group');
        const currentIdx = Array.from(followups).indexOf(this.closest('.followup-group'));
        if (this.value === '0') {
            if (currentIdx === followups.length - 1) addFollowupField(row);
        } else {
            for (let i = followups.length - 1; i > currentIdx; i--) followups[i].remove();
        }
    });
}

function clearFollowups(row) {
    getFollowupWrapper(row).innerHTML = '';
}

function getFollowupWrapper(row) {
    return row.nextElementSibling.querySelector('.followup-wrapper');
}

// Saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    // Bahan baku: tambah 1 baris default
    addMaterialRow();

    // Semua row yang sudah ada (kemasan, mesin, ruangan)
    document.querySelectorAll('tr').forEach(row => {
        if (row.querySelector('.condition-select')) {
            initEvents(row);
        }
    });
});
</script>
@endsection