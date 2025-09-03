@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report Tanggal {{ $report->date }} Shift {{ $report->shift }}</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_packaging_verifs.store-detail', $report->uuid) }}">
                @csrf

                <h5 class="mb-3"><strong>Detail Produk</strong></h5>
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Jam</th>
                            <th>Produk</th>
                            <th>Kode Produksi</th>
                            <th>Best Before</th>
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

                {{-- In Cutting --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>In Cutting</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][in_cutting]"
                                value="Manual" required>
                            <label class="form-check-label">Manual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][in_cutting]"
                                value="Mesin">
                            <label class="form-check-label">Mesin</label>
                        </div>
                    </div>
                </div>

                {{-- Proses Pengemasan --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Proses Pengemasan</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][packaging]"
                                value="Thermoformer" required>
                            <label class="form-check-label">Thermoformer</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][checklist][packaging]"
                                value="Manual">
                            <label class="form-check-label">Manual</label>
                        </div>
                    </div>
                </div>

                {{-- Sealing Condition --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Hasil Sealing: Kondisi Seal</strong></div>
                    <div class="card-body p-2">
                        <div class="row">
                            @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                                <label class="small">Kondisi Seal {{ $i }}</label>
                                <select name="details[0][checklist][sealing_condition_{{ $i }}]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                        </div>
                        @endfor
                    </div>
                </div>
        </div>

        {{-- Sealing Vacuum --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Hasil Sealing: Vacuum</strong></div>
            <div class="card-body p-2">
                <div class="row">
                    @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                        <label class="small">Vacuum {{ $i }}</label>
                        <select name="details[0][checklist][sealing_vacuum_{{ $i }}]" class="form-control">
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Isi Per-Pack --}}
    <div class="card mb-3">
        <div class="card-header p-2"><strong>Isi Per-Pack</strong></div>
        <div class="card-body p-2">
            <div class="row">
                @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                    <label class="small">Isi Per-Pack {{ $i }}</label>
                    <input type="number" name="details[0][checklist][content_per_pack_{{ $i }}]" class="form-control">
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- Berat Produk --}}
<div class="card mb-3">
    <div class="card-header p-2"><strong>Berat Produk Per Plastik</strong></div>
    <div class="card-body p-2">
        <div class="row mb-2">
            <div class="col-md-2">
                <label class="small">Standar</label>
                <input type="number" step="0.01" name="details[0][checklist][standard_weight]" class="form-control">
            </div>
        </div>
        <div class="row">
            @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                <label class="small">Aktual {{ $i }}</label>
                <input type="number" step="0.01" name="details[0][checklist][actual_weight_{{ $i }}]"
                    class="form-control">
        </div>
        @endfor
    </div>
</div>
</div>

<button type="submit" class="btn btn-success">Simpan Detail</button>
</form>
</div>
</div>
</div>
@endsection