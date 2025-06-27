@extends('layouts.app')

@section('content')
@php
$conditionOptions_1_8 = [
1 => '1 - Bersih, tidak ada sisa bahan/kemasan sebelumnya',
2 => '2 - Ada sisa bahan/kemasan sebelumnya',
3 => '3 - Bebas dari kontaminan dan bahan sebelumnya',
4 => '4 - Ada kontaminan atau sisa bahan sebelumnya',
5 => '5 - Bebas dari potensi kontaminasi allergen',
6 => '6 - Ada potensi kontaminasi allergen',
7 => '7 - Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang',
8 => '8 - Tidak bersih, ada kontaminan/kotoran, tercium bau menyimpang',
];

$conditionOptions_3_8 = array_slice($conditionOptions_1_8, 2, 6, true); // dari key 3 sampai 8
@endphp

<div class="container-fluid">
    <form action="{{ route('report_product_changes.store') }}" method="POST">
        @csrf

        {{-- HEADER --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Form Verifikasi Pergantian Produk</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-3 mb-2">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Produk</label>
                    <select name="product_uuid" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" required>
                </div>
            </div>
        </div>

        {{-- SISA BAHAN DAN KEMASAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Sisa Bahan dan Kemasan</h5>
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
                        @foreach ($materialItems as $i => $item)
                        <tr>
                            <td class="align-middle">
                                <input type="hidden" name="material_leftovers[{{ $i }}][item]" value="{{ $item }}">
                                {{ $item }}
                            </td>
                            <td>
                                <select name="material_leftovers[{{ $i }}][condition]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_1_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="material_leftovers[{{ $i }}][corrective_action]"
                                    class="form-control"></td>
                            <td><input type="text" name="material_leftovers[{{ $i }}][verification]"
                                    class="form-control"></td>
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
                            <td class="align-middle">
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
                            <td class="align-middle">
                                <input type="hidden" name="sections[{{ $i }}][section_uuid]"
                                    value="{{ $section->uuid }}">
                                {{ $section->section_name }}
                            </td>
                            <td>
                                <select name="sections[{{ $i }}][condition]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="sections[{{ $i }}][corrective_action]" class="form-control">
                            </td>
                            <td><input type="text" name="sections[{{ $i }}][verification]" class="form-control"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="text-end">
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>
@endsection