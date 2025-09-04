@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Report Packaging Verif</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_packaging_verifs.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
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
                            <!-- <th>Kode Produksi</th>
                            <th>Best Before</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}"></td>
                            <td>
                                <select name="details[0][product_uuid]" class="form-control">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}
                                        {{ $product->nett_weight }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <!-- <td><input type="text" name="details[0][production_code]" class="form-control"></td>
                            <td><input type="date" name="details[0][expired_date]" class="form-control"></td> -->
                        </tr>
                    </tbody>
                </table>

                <div class="card mt-3 mb-3">
                    <div class="card-header p-2">
                        <strong>Upload Foto</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Upload MD BPOM</label>
                                <input type="file" name="details[0][upload_md]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Upload QR Code</label>
                                <input type="file" name="details[0][upload_qr]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Upload Kode Produksi & Best Before</label>
                                <input type="file" name="details[0][upload_ed]" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- In Cutting hanya 1 --}}
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

                {{-- Proses Pengemasan hanya 1 --}}
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

                {{-- Sealing Condition 5x --}}
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

        {{-- Sealing Vacuum 5x --}}
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

    {{-- Isi Per-Pack 5x --}}
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

<div class="card mb-3">
    <div class="card-header p-2"><strong>Panjang Produk Per Pcs</strong></div>
    <div class="card-body p-2">
        <div class="row mb-2">
            <div class="col-md-2">
                <label class="small">Standar</label>
                <input type="number" step="0.01" name="details[0][checklist][standard_long_pcs]" class="form-control">
            </div>
        </div>
        <div class="row">
            @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                <label class="small">Aktual {{ $i }}</label>
                <input type="number" step="0.01" name="details[0][checklist][actual_long_pcs_{{ $i }}]"
                    class="form-control actual-input">
        </div>
        @endfor
        <div class="col-md-2">
            <label class="small">Rata-Rata Panjang</label>
            <input type="number" step="0.01" name="details[0][checklist][avg_long_pcs]" class="form-control"
                id="avg-long-pcs" readonly>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header p-2"><strong>Berat Produk Per Pcs</strong></div>
    <div class="card-body p-2">
        <div class="row mb-2">
            <div class="col-md-2">
                <label class="small">Standar</label>
                <input type="number" step="0.01" name="details[0][checklist][standard_weight_pcs]" class="form-control">
            </div>
        </div>
        <div class="row">
            @for($i=1; $i<=5; $i++) <div class="col-md-2 mb-2">
                <label class="small">Aktual {{ $i }}</label>
                <input type="number" step="0.01" name="details[0][checklist][actual_weight_pcs_{{ $i }}]"
                    class="form-control actual-input-wpcs">
        </div>
        @endfor
        <div class="col-md-2">
            <label class="small">Rata-Rata Berat</label>
            <input type="number" step="0.01" name="details[0][checklist][avg_weight_pcs]" class="form-control"
                id="avg-weight-pcs" readonly>
        </div>
    </div>
</div>

{{-- Berat Produk --}}
<div class="card mb-3">
    <div class="card-header p-2"><strong>Berat Produk Per Pack</strong></div>
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
                    class="form-control actual-input-w">
        </div>
        @endfor
        <div class="col-md-2">
            <label class="small">Rata-Rata Berat</label>
            <input type="number" step="0.01" name="details[0][checklist][avg_weight]" class="form-control"
                id="avg-weight" readonly>
        </div>
    </div>
</div>

<div class="card mb-3 mt-3">
    <div class="card-body p-2 row">
        <div class="col-md-6">
            <label class="small">Hasil Verifikasi MD</label>
            <select name="details[0][checklist][verif_md]" class="form-control">
                <option value="OK">OK</option>
                <option value="Tidak OK">Tidak OK</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="small">Keterangan</label>
            <input type="text" name="details[0][checklist][notes]" class="form-control">
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actualInputs = document.querySelectorAll('.actual-input');
    const avgInput = document.getElementById('avg-long-pcs');

    function calculateAverage() {
        let sum = 0;
        let count = 0;

        actualInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                sum += value;
                count++;
            }
        });

        const avg = count > 0 ? (sum / count).toFixed(2) : '';
        avgInput.value = avg;
    }

    actualInputs.forEach(input => {
        input.addEventListener('input', calculateAverage);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const actualInputs = document.querySelectorAll('.actual-input-wpcs');
    const avgInput = document.getElementById('avg-weight-pcs');

    function calculateAverage() {
        let sum = 0;
        let count = 0;

        actualInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                sum += value;
                count++;
            }
        });

        const avg = count > 0 ? (sum / count).toFixed(2) : '';
        avgInput.value = avg;
    }

    actualInputs.forEach(input => {
        input.addEventListener('input', calculateAverage);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const actualInputs = document.querySelectorAll('.actual-input-w');
    const avgInput = document.getElementById('avg-weight');

    function calculateAverage() {
        let sum = 0;
        let count = 0;

        actualInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                sum += value;
                count++;
            }
        });

        const avg = count > 0 ? (sum / count).toFixed(2) : '';
        avgInput.value = avg;
    }

    actualInputs.forEach(input => {
        input.addEventListener('input', calculateAverage);
    });
});
</script>
@endsection