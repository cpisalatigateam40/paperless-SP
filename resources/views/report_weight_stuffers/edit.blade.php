@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow mb-4">
            <div class="card-header">Edit Laporan Verifikasi Berat Stuffer</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ $report->date ? \Carbon\Carbon::parse($report->date)->toDateString() : '' }}"
                        required>
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                </div>
            </div>
        </div>

        <div id="productDetails">
            @foreach($report->details as $i => $detail)
            @php
            $machineType = $detail->townsend ? 'townsend' : ($detail->hitech ? 'hitech' : '');
            $machineData = $machineType === 'townsend' ? $detail->townsend : $detail->hitech;
            @endphp

            <div class="card detail-block mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>Data Produk #{{ $i + 1 }}</strong>
                </div>
                <div class="card-body">

                    {{-- DATA PRODUK --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Nama Produk</label>
                            <select name="details[{{ $i }}][product_uuid]"
                                class="form-select form-control select2-product" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                @php
                                $standard = $standards->where('product_uuid', $product->uuid)->first();
                                @endphp
                                <option value="{{ $product->uuid }}" @if($product->uuid == $detail->product_uuid)
                                    selected @endif
                                    @if($standard)
                                    data-long-min="{{ $standard->long_min }}"
                                    data-long-max="{{ $standard->long_max }}"
                                    data-diameter="{{ $standard->diameter }}"
                                    data-weight-standard="{{ $standard->weight_max }}"
                                    @endif>
                                    {{ $product->product_name }} - {{ $product->nett_weight }} g
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                value="{{ $detail->production_code }}" required>
                        </div>

                        <div class="col-md-4">
                            <label>Waktu Proses</label>
                            <input type="time" name="details[{{ $i }}][time]" class="form-control"
                                value="{{ $detail->time }}" required>
                        </div>
                    </div>

                    {{-- MESIN --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Nama Mesin</label>
                            <select name="details[{{ $i }}][machine]" class="form-select form-control" required>
                                <option value="">-- Pilih Mesin --</option>
                                <option value="townsend" {{ $machineType === 'townsend' ? 'selected' : '' }}>Townsend
                                </option>
                                <option value="hitech" {{ $machineType === 'hitech' ? 'selected' : '' }}>Hitech</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Kecepatan Stuffer (rpm)</label>
                            <input type="number" name="details[{{ $i }}][stuffer_speed]" class="form-control"
                                value="{{ $machineData->stuffer_speed ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label>Catatan</label>
                            <input type="text" name="details[{{ $i }}][notes]" class="form-control"
                                value="{{ $machineData->notes ?? '' }}">
                        </div>
                    </div>

                    <hr>

                    {{-- CASING --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Diameter Casing (mm)</label>
                            <input type="number" name="details[{{ $i }}][cases][0][actual_case_2]" class="form-control"
                                value="{{ $detail->cases->first()->actual_case_2 ?? '' }}">
                        </div>
                    </div>

                    {{-- BERAT --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Standar Berat (gr)</label>
                            <input type="text" name="details[{{ $i }}][weight_standard]" class="form-control"
                                value="{{ $detail->weight_standard }}" required placeholder="contoh: 12-13">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Berat Aktual (gr)</label>
                            <div id="weightWrapper-{{ $i }}" class="row g-2">
                                @foreach($detail->weights as $index => $weight)
                                <div class="col-md-3">
                                    <label>Berat {{ $index + 1 }}</label>
                                    <input type="number" step="0.01"
                                        name="details[{{ $i }}][weights][0][actual_weight_{{ $index + 1 }}]"
                                        class="form-control" value="{{ $weight->actual_weight }}">
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2 add-weight"
                                data-index="{{ $i }}">
                                + Tambah Berat
                            </button>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Rata-rata Berat</label>
                            <input type="number" step="0.01" name="details[{{ $i }}][avg_weight]" class="form-control"
                                value="{{ $machineData->avg_weight ?? '' }}" readonly>
                        </div>
                    </div>

                    {{-- PANJANG --}}
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <label>Standar Panjang</label>
                            <input type="text" name="details[{{ $i }}][long_standard]" class="form-control"
                                value="{{ $detail->long_standard }}" required placeholder="contoh: 12-13">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Panjang Aktual</label>
                            <div id="longWrapper-{{ $i }}" class="row g-2">
                                @foreach($detail->weights as $index => $weight)
                                <div class="col-md-3">
                                    <label>Panjang {{ $index + 1 }}</label>
                                    <input type="number" step="0.01"
                                        name="details[{{ $i }}][weights][0][actual_long_{{ $index + 1 }}]"
                                        class="form-control" value="{{ $weight->actual_long }}">
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2 add-long" data-index="{{ $i }}">
                                + Tambah Panjang
                            </button>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Rata-rata Panjang</label>
                            <input type="number" step="0.01" name="details[{{ $i }}][avg_long]" class="form-control"
                                value="{{ $machineData->avg_long ?? '' }}" readonly>
                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-success float-end">Update Laporan</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('click', function(e) {
    // Tambah Berat
    if (e.target.classList.contains('add-weight')) {
        const index = e.target.dataset.index;
        const wrapper = document.getElementById(`weightWrapper-${index}`);
        const currentCount = wrapper.querySelectorAll('input').length + 1;

        const col = document.createElement('div');
        col.className = 'col-md-3';
        col.innerHTML = `
            <label>Berat ${currentCount}</label>
            <input type="number" step="0.01" 
                   name="details[${index}][weights][0][actual_weight_${currentCount}]"
                   class="form-control">
        `;
        wrapper.appendChild(col);
    }

    // Tambah Panjang
    if (e.target.classList.contains('add-long')) {
        const index = e.target.dataset.index;
        const wrapper = document.getElementById(`longWrapper-${index}`);
        const currentCount = wrapper.querySelectorAll('input').length + 1;

        const col = document.createElement('div');
        col.className = 'col-md-3';
        col.innerHTML = `
            <label>Panjang ${currentCount}</label>
            <input type="number" step="0.01" 
                   name="details[${index}][weights][0][actual_long_${currentCount}]"
                   class="form-control">
        `;
        wrapper.appendChild(col);
    }
});

document.addEventListener('input', function(e) {
    const block = e.target.closest('.detail-block');
    if (!block) return;

    const hitungRata = (prefix, avgField) => {
        // FIX: hapus quote ekstra -> sebelumnya: ..._"]"]
        const inputs = block.querySelectorAll(`input[name*="[actual_${prefix}_"]`);
        let sum = 0,
            count = 0;

        inputs.forEach(inp => {
            const v = parseFloat(inp.value);
            if (!isNaN(v)) {
                sum += v;
                count++;
            }
        });

        const avgInput = block.querySelector(`input[name*="[${avgField}]"]`);
        if (avgInput) avgInput.value = count ? (sum / count).toFixed(2) : '';
    };

    hitungRata('weight', 'avg_weight');
    hitungRata('long', 'avg_long');
});
</script>
@endsection