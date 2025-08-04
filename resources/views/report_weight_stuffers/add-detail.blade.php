@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.store-detail', $report->uuid) }}" method="POST">
        @csrf

        {{-- Informasi Header --}}
        <div class="card shadow mb-4">
            <div class="card-header">Laporan Tanggal {{ $report->date }} - Shift {{ $report->shift }}</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" class="form-control" value="{{ $report->date }}" disabled>
                </div>
                <div class="col-md-2">
                    <label>Shift</label>
                    <input type="text" class="form-control" value="{{ $report->shift }}" disabled>
                </div>
            </div>
        </div>

        {{-- Detail Produk --}}
        <div id="productDetails">
            <div class="card detail-block mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>Data Produk</strong>
                    <!-- <button type="button" class="btn btn-sm btn-danger remove-detail">Hapus</button> -->
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Nama Produk</label>
                            <select name="details[0][product_uuid]" class="form-control product-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                @php
                                $standard = $standards->where('product_uuid', $product->uuid)->first();
                                @endphp
                                <option value="{{ $product->uuid }}" @if($standard)
                                    data-long-min="{{ $standard->long_min }}" data-long-max="{{ $standard->long_max }}"
                                    data-diameter="{{ $standard->diameter }}"
                                    data-weight-standard="{{ $standard->weight_max }}" @endif>
                                    {{ $product->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[0][production_code]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Waktu Proses</label>
                            <input type="time" name="details[0][time]" class="form-control" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2">Mesin</th>
                                    <th rowspan="2">Speed (rpm)</th>
                                    <th colspan="2">Ukuran Casing</th>
                                    <!-- <th rowspan="2">Jumlah Trolley</th> -->
                                    <th rowspan="2">Standar Berat (gr)</th>
                                    <th colspan="3">Berat Aktual (gr)</th>
                                    <th rowspan="2">Rata-rata</th>
                                    <th rowspan="2">Catatan</th>
                                </tr>
                                <tr>
                                    <th>Panjang</th>
                                    <th>Diameter</th>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(['townsend', 'hitech'] as $machine)
                                <tr>
                                    <td class="text-capitalize">{{ $machine }}</td>
                                    <td><input type="number" name="details[0][{{ $machine }}][stuffer_speed]"
                                            class="form-control form-control-sm"></td>
                                    <td>
                                        <input type="number" name="details[0][cases][{{ $loop->index }}][actual_case_1]"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" name="details[0][cases][{{ $loop->index }}][actual_case_2]"
                                            class="form-control form-control-sm">
                                    </td>
                                    <!-- <td><input type="number" name="details[0][{{ $machine }}][trolley_total]"
                                            class="form-control form-control-sm"></td> -->

                                    @if ($loop->first)
                                    <td rowspan="2">
                                        <input type="number" step="0.01" name="details[0][weight_standard]"
                                            class="form-control form-control-sm" required>
                                    </td>
                                    @endif

                                    <td><input type="number" step="0.01"
                                            name="details[0][weights][{{ $loop->index }}][actual_weight_1]"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01"
                                            name="details[0][weights][{{ $loop->index }}][actual_weight_2]"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01"
                                            name="details[0][weights][{{ $loop->index }}][actual_weight_3]"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="details[0][{{ $machine }}][avg_weight]"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="text" name="details[0][{{ $machine }}][notes]"
                                            class="form-control form-control-sm"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <!-- <button type="button" class="btn btn-secondary" id="addProductDetail">+ Tambah Produk</button> -->
            <button type="submit" class="btn btn-success float-end">Simpan Detail</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let index = 1;
const template = document.querySelector('.detail-block');

document.getElementById('addProductDetail')?.addEventListener('click', function() {
    const clone = template.cloneNode(true);
    clone.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.name) el.name = el.name.replace(/\[0\]/g, `[${index}]`);
        if (el.type !== 'hidden') el.value = '';
    });
    document.getElementById('productDetails').appendChild(clone);
    index++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        const block = e.target.closest('.detail-block');
        if (document.querySelectorAll('.detail-block').length > 1) {
            block.remove();
        }
    }
});

document.addEventListener('change', function(e) {
    if (e.target.matches('.product-select')) {
        const option = e.target.selectedOptions[0];
        const cardBody = e.target.closest('.card-body');

        if (option && cardBody) {
            const weightStandard = option.dataset.weightStandard;
            const diameter = option.dataset.diameter;
            const longMax = option.dataset.longMax;

            const weightStandardInput = cardBody.querySelector('input[name$="[weight_standard]"]');
            if (weightStandard) {
                weightStandardInput.value = weightStandard;
            } else {
                weightStandardInput.value = '';
            }

            cardBody.querySelectorAll('input[name*="[actual_case_1]"]').forEach(input => {
                input.value = longMax || '';
            });
            cardBody.querySelectorAll('input[name*="[actual_case_2]"]').forEach(input => {
                input.value = diameter || '';
            });

            // Reset field lain
            cardBody.querySelectorAll('input').forEach(input => {
                const name = input.name;
                if (name.endsWith('[weight_standard]')) return;
                if (name.includes('[actual_case_1]')) return;
                if (name.includes('[actual_case_2]')) return;
                input.value = '';
            });
        }
    }
});

document.addEventListener('input', function(e) {
    const tr = e.target.closest('tr');
    if (!tr) return;

    const weightInputs = [
        tr.querySelector('input[name*="[actual_weight_1]"]'),
        tr.querySelector('input[name*="[actual_weight_2]"]'),
        tr.querySelector('input[name*="[actual_weight_3]"]')
    ];

    let sum = 0;
    let count = 0;

    weightInputs.forEach(input => {
        if (input && input.value !== '') {
            const v = parseFloat(input.value);
            if (!isNaN(v)) {
                sum += v;
                count++;
            }
        }
    });

    const avgInput = tr.querySelector('input[name*="[avg_weight]"]');
    if (avgInput) {
        if (count > 0) {
            avgInput.value = (sum / count).toFixed(2);
        } else {
            avgInput.value = '';
        }
    }
});
</script>
@endsection