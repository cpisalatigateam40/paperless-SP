@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Verifikasi Pemeriksaan Kemasan Plastik</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_packaging_verifs.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                    </div>
                    <div class="col">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control">
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><strong>Detail Produk</strong></h5>

                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Jam</th>
                            <th>Produk</th>
                            <th>Kode Produksi</th>
                            <th>Expired date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}"></td>
                            <td>
                                <select name="details[0][product_uuid]" class="form-control">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="details[0][production_code]" class="form-control"></td>
                            <td><input type="date" name="details[0][expired_date]" class="form-control"></td>
                        </tr>
                    </tbody>
                </table>

                <hr>

                @php
                $checklistGroups = [
                'in_cutting_manual' => 'In Cutting Manual',
                'in_cutting_machine' => 'In Cutting Mesin',
                'packaging_thermoformer' => 'Proses Pengemasan Thermoformer',
                'packaging_manual' => 'Proses Pengemasan Manual',
                'sealing_condition' => 'Hasil Sealing Kondisi Seal',
                'sealing_vacuum' => 'Hasil Sealing Vacum',
                ];
                @endphp

                @foreach($checklistGroups as $field => $label)
                <div class="card mb-3">
                    <div class="card-header p-2">
                        <strong>{{ $label }}</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            @for($i = 1; $i <= 5; $i++) <div class="col-md-2 mb-2">
                                <label class="small">{{ $label }} {{ $i }}</label>
                                <select name="details[0][checklist][{{ $field }}_{{ $i }}]" class="form-control">
                                    <option value="">Pilih Hasil</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                        </div>
                        @endfor
                    </div>
                </div>
        </div>
        @endforeach

        <div class="card mb-3">
            <div class="card-header p-2">
                <strong>Isi Per-Pack</strong>
            </div>
            <div class="card-body p-2">
                <div class="row">
                    @for($i = 1; $i <= 5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Isi Per-Pack {{ $i }}</label>
                        <input type="number" name="details[0][checklist][content_per_pack_{{ $i }}]"
                            class="form-control">
                </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header p-2">
            <strong>Berat Produk Per Plastik</strong>
        </div>
        <div class="card-body p-2">
            <div class="row mb-2">
                <div class="col-md-2">
                    <label class="small">Standar</label>
                    <input type="number" step="0.01" name="details[0][checklist][standard_weight]" class="form-control">
                </div>
            </div>
            <div class="row">
                @for($i = 1; $i <= 5; $i++) <div class="col-md-2 mb-2">
                    <label class="small">Aktual {{ $i }}</label>
                    <input type="number" step="0.01" name="details[0][checklist][actual_weight_{{ $i }}]"
                        class="form-control">
            </div>
            @endfor
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row">
            <div class="col-md-3">
                <label>
                    <input type="checkbox" name="details[0][qc_verif]" value="1"> Verifikasi QC
                </label>
            </div>
            <div class="col-md-3">
                <label>
                    <input type="checkbox" name="details[0][kr_verif]" value="1"> Verifikasi KR
                </label>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-success">Simpan Report</button>
</form>
</div>
</div>
</div>
@endsection