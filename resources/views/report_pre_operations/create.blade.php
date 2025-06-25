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
                                <select name="packagings[{{ $i }}][condition]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_1_2 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="packagings[{{ $i }}][corrective_action]" class="form-control">
                            </td>
                            <td><input type="text" name="packagings[{{ $i }}][verification]" class="form-control"></td>
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
                                <select name="equipments[{{ $i }}][condition]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="equipments[{{ $i }}][corrective_action]" class="form-control">
                            </td>
                            <td><input type="text" name="equipments[{{ $i }}][verification]" class="form-control"></td>
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
                                <select name="rooms[{{ $i }}][condition]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="rooms[{{ $i }}][corrective_action]" class="form-control"></td>
                            <td><input type="text" name="rooms[{{ $i }}][verification]" class="form-control"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="text-end mb-5">
            <button type="submit" class="btn btn-success">Simpan</button>
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

    let conditionSelect = `<select name="materials[${materialIndex}][condition]" class="form-control">
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
                <td><input type="text" name="materials[${materialIndex}][corrective_action]" class="form-control"></td>
                <td><input type="text" name="materials[${materialIndex}][verification]" class="form-control"></td>
            `;

    tbody.appendChild(row);
    materialIndex++;
}

// Tambahkan 1 baris default saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    addMaterialRow();
});
</script>
@endsection