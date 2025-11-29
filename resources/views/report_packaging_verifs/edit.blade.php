@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Verifikasi Kemasan Plastik</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_packaging_verifs.update', $report->uuid) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
                    </div>
                    <div class="col">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><strong>Detail Produk</strong></h5>

                @foreach($details as $i => $detail)
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Jam</th>
                            <th>Produk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="time" name="details[{{ $i }}][time]" class="form-control"
                                    value="{{ $detail->time }}"></td>
                            <td>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control select2-product">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        {{ $product->uuid == $detail->product_uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }} - {{ $product->nett_weight }} g
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Upload Foto --}}
                <div class="card mt-3 mb-3">
                    <div class="card-header p-2">
                        <strong>Upload Foto</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <!-- <div class="col-md-4">
                                <label class="form-label">Upload MD BPOM</label>
                                @if($detail->upload_md)
                                <a href="{{ asset('storage/'.$detail->upload_md) }}" target="_blank"
                                    class="d-block mb-1 text-primary">Lihat File Lama</a>
                                @endif
                                <input type="file" name="details[{{ $i }}][upload_md]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Upload QR Code</label>
                                @if($detail->upload_qr)
                                <a href="{{ asset('storage/'.$detail->upload_qr) }}" target="_blank"
                                    class="d-block mb-1 text-primary">Lihat File Lama</a>
                                @endif
                                <input type="file" name="details[{{ $i }}][upload_qr]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Upload Kode Produksi & Best Before</label>
                                @if($detail->upload_ed)
                                <a href="{{ asset('storage/'.$detail->upload_ed) }}" target="_blank"
                                    class="d-block mb-1 text-primary">Lihat File Lama</a>
                                @endif
                                <input type="file" name="details[{{ $i }}][upload_ed]" class="form-control">
                            </div> -->
                            <div class="col-md-4">
                                <label class="form-label">Upload MD BPOM (Multiple)</label>

                                @if(!empty($detail->upload_md_multi))
                                    @php
                                        $files = json_decode($detail->upload_md_multi, true);
                                    @endphp

                                    <div class="mb-2">
                                        @foreach($files as $file)
                                            <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $file) }}" alt="Preview" width="60" height="60"
                                                    style="object-fit: cover; border: 1px solid #ccc; border-radius: 4px; margin-right: 6px;">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <input type="file" name="details[{{ $i }}][upload_md_multi][]" class="form-control" multiple>
                            </div>


                        </div>
                    </div>
                </div>

                {{-- In Cutting --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>In Cutting</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        @php
                        $checklist = $detail->checklist ?? null;
                        $inCut = '';
                        if ($checklist) {
                        if ($checklist->in_cutting_manual_1 === 'OK') {
                        $inCut = 'Manual';
                        } elseif ($checklist->in_cutting_machine_1 === 'OK') {
                        $inCut = 'Mesin';
                        }
                        }
                        @endphp
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[{{ $i }}][checklist][in_cutting]"
                                value="Manual" {{ $inCut == 'Manual' ? 'checked' : '' }}>
                            <label class="form-check-label">Manual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[{{ $i }}][checklist][in_cutting]"
                                value="Mesin" {{ $inCut == 'Mesin' ? 'checked' : '' }}>
                            <label class="form-check-label">Mesin</label>
                        </div>
                    </div>
                </div>

                {{-- Proses Pengemasan --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Proses Pengemasan</strong></div>
                    <div class="card-body p-2">
                        <label class="small d-block">Pilih Salah Satu</label>
                        @php
                        $pack = '';
                        if ($checklist) {
                        if ($checklist->packaging_thermoformer_1 === 'OK') {
                        $pack = 'Thermoformer';
                        } elseif ($checklist->packaging_manual_1 === 'OK') {
                        $pack = 'Manual';
                        }
                        }
                        @endphp
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[{{ $i }}][checklist][packaging]"
                                value="Thermoformer" {{ $pack == 'Thermoformer' ? 'checked' : '' }}>
                            <label class="form-check-label">Thermoformer</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[{{ $i }}][checklist][packaging]"
                                value="Manual" {{ $pack == 'Manual' ? 'checked' : '' }}>
                            <label class="form-check-label">Manual</label>
                        </div>
                    </div>
                </div>


                {{-- Sampling Kemasan --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Sampling Kemasan</strong></div>
                    <div class="card-body p-2 row">
                        <div class="col-md-4">
                            <label class="small">Jumlah Sampling</label>
                            <input type="number" name="details[{{ $i }}][checklist][sampling_amount]"
                                class="form-control" value="{{ $detail->checklist->sampling_amount ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="small">Satuan</label>
                            <select name="details[{{ $i }}][checklist][unit]" class="form-control">
                                <option value="kemasan"
                                    {{ ($detail->checklist->unit ?? '') == 'kemasan' ? 'selected' : '' }}>kemasan
                                </option>
                                <option value="pack" {{ ($detail->checklist->unit ?? '') == 'pack' ? 'selected' : '' }}>
                                    pack</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small">Hasil Sampling</label>
                            <select name="details[{{ $i }}][checklist][sampling_result]" class="form-control">
                                <option value="OK"
                                    {{ ($detail->checklist->sampling_result ?? '') == 'OK' ? 'selected' : '' }}>OK
                                </option>
                                <option value="Tidak OK"
                                    {{ ($detail->checklist->sampling_result ?? '') == 'Tidak OK' ? 'selected' : '' }}>
                                    Tidak OK</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Sealing Condition --}}
                <div class="card mb-3">
                    <div class="card-header p-2"><strong>Hasil Sealing: Kondisi Seal</strong></div>
                    <div class="card-body p-2 row">
                        @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
                            <label class="small">Kondisi Seal {{ $x }}</label>
                            <select name="details[{{ $i }}][checklist][sealing_condition_{{ $x }}]"
                                class="form-control">
                                <option value="OK"
                                    {{ ($detail->checklist->{'sealing_condition_'.$x} ?? '') == 'OK' ? 'selected' : '' }}>
                                    OK</option>
                                <option value="Tidak OK"
                                    {{ ($detail->checklist->{'sealing_condition_'.$x} ?? '') == 'Tidak OK' ? 'selected' : '' }}>
                                    Tidak OK</option>
                            </select>
                    </div>
                    @endfor
                </div>
        </div>

        {{-- Sealing Vacuum --}}
        <div class="card mb-3">
            <div class="card-header p-2"><strong>Hasil Sealing: Vacuum</strong></div>
            <div class="card-body p-2 row">
                @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
                    <label class="small">Vacuum {{ $x }}</label>
                    <select name="details[{{ $i }}][checklist][sealing_vacuum_{{ $x }}]" class="form-control">
                        <option value="OK"
                            {{ ($detail->checklist->{'sealing_vacuum_'.$x} ?? '') == 'OK' ? 'selected' : '' }}>OK
                        </option>
                        <option value="Tidak OK"
                            {{ ($detail->checklist->{'sealing_vacuum_'.$x} ?? '') == 'Tidak OK' ? 'selected' : '' }}>
                            Tidak OK</option>
                    </select>
            </div>
            @endfor
        </div>
    </div>

    {{-- Panjang Produk --}}
    <div class="card mb-3">
        <div class="card-header p-2"><strong>Panjang Produk Per Pcs</strong></div>
        <div class="card-body p-2 row">
            <div class="col-md-2">
                <label class="small">Standar</label>
                <input type="text" name="details[{{ $i }}][checklist][standard_long_pcs]" class="form-control"
                    value="{{ $detail->checklist->standard_long_pcs ?? '' }}">
            </div>
            @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
                <label class="small">Aktual {{ $x }}</label>
                <input type="number" step="0.01" name="details[{{ $i }}][checklist][actual_long_pcs_{{ $x }}]"
                    class="form-control actual-input" value="{{ $detail->checklist->{'actual_long_pcs_'.$x} ?? '' }}">
        </div>
        @endfor
        <div class="col-md-2">
            <label class="small">Rata-Rata Panjang</label>
            <input type="number" step="0.01" name="details[{{ $i }}][checklist][avg_long_pcs]" class="form-control"
                id="avg-long-pcs" value="{{ $detail->checklist->avg_long_pcs ?? '' }}" readonly>
        </div>
    </div>
</div>

{{-- Berat Produk Per Pcs --}}
<div class="card mb-3">
    <div class="card-header p-2"><strong>Berat Produk Per Pcs</strong></div>
    <div class="card-body p-2 row">
        <div class="col-md-2">
            <label class="small">Standar</label>
            <input type="text" name="details[{{ $i }}][checklist][standard_weight_pcs]" class="form-control"
                value="{{ $detail->checklist->standard_weight_pcs ?? '' }}">
        </div>
        @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
            <label class="small">Aktual {{ $x }}</label>
            <input type="number" step="0.01" name="details[{{ $i }}][checklist][actual_weight_pcs_{{ $x }}]"
                class="form-control actual-input-wpcs"
                value="{{ $detail->checklist->{'actual_weight_pcs_'.$x} ?? '' }}">
    </div>
    @endfor
    <div class="col-md-2">
        <label class="small">Rata-Rata Berat</label>
        <input type="number" step="0.01" name="details[{{ $i }}][checklist][avg_weight_pcs]" class="form-control"
            id="avg-weight-pcs" value="{{ $detail->checklist->avg_weight_pcs ?? '' }}" readonly>
    </div>
</div>
</div>

{{-- Isi Per Pack --}}
<div class="card mb-3">
    <div class="card-header p-2"><strong>Isi Per Pack</strong></div>
    <div class="card-body p-2 row">
        @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
            <label class="small">Aktual {{ $x }}</label>
            <input type="number" name="details[{{ $i }}][checklist][content_per_pack_{{ $x }}]" class="form-control"
                value="{{ $detail->checklist->{'content_per_pack_'.$x} ?? '' }}">
    </div>
    @endfor
</div>
</div>

{{-- Berat Produk --}}
<div class="card mb-3">
    <div class="card-header p-2"><strong>Berat Produk Per Pack</strong></div>
    <div class="card-body p-2 row">
        <div class="col-md-2">
            <label class="small">Standar</label>
            <input type="text" name="details[{{ $i }}][checklist][standard_weight]" class="form-control"
                value="{{ $detail->checklist->standard_weight ?? '' }}">
        </div>
        @for($x=1; $x<=5; $x++) <div class="col-md-2 mb-2">
            <label class="small">Aktual {{ $x }}</label>
            <input type="number" step="0.01" name="details[{{ $i }}][checklist][actual_weight_{{ $x }}]"
                class="form-control actual-input-w" value="{{ $detail->checklist->{'actual_weight_'.$x} ?? '' }}">
    </div>
    @endfor
    <div class="col-md-2">
        <label class="small">Rata-Rata Berat</label>
        <input type="number" step="0.01" name="details[{{ $i }}][checklist][avg_weight]" class="form-control"
            id="avg-weight" value="{{ $detail->checklist->avg_weight ?? '' }}" readonly>
    </div>
</div>
</div>

<div class="card mb-3 mt-3">
    <div class="card-body p-2 row">
        <div class="col-md-6">
            <label class="small">Hasil Verifikasi MD</label>
            <select name="details[{{ $i }}][checklist][verif_md]" class="form-control">
                <option value="OK" {{ ($detail->checklist->verif_md ?? '') == 'OK' ? 'selected' : '' }}>OK</option>
                <option value="Tidak OK" {{ ($detail->checklist->verif_md ?? '') == 'Tidak OK' ? 'selected' : '' }}>
                    Tidak OK</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="small">Keterangan</label>
            <input type="text" name="details[{{ $i }}][checklist][notes]" class="form-control"
                value="{{ $detail->checklist->notes ?? '' }}">
        </div>
    </div>
</div>
@endforeach

<button type="submit" class="btn btn-success">Update Report</button>
<a href="{{ route('report_packaging_verifs.index') }}" class="btn btn-secondary">Batal</a>
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