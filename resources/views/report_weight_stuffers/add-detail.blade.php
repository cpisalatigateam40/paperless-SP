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
                <div class="card-header">
                    <strong>Data Produk</strong>
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

                    {{-- Mesin & Input Detail --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label>Mesin</label>
                            <select name="details[0][machine]" class="form-control" required>
                                <option value="">-- Pilih Mesin --</option>
                                <option value="townsend">Townsend</option>
                                <option value="hitech">Hitech</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Kecepatan Stuffer (rpm)</label>
                            <input type="number" name="details[0][stuffer_speed]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Catatan</label>
                            <input type="text" name="details[0][notes]" class="form-control">
                        </div>

                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label>Ukuran Casing - Panjang</label>
                            <input type="number" name="details[0][cases][0][actual_case_1]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Ukuran Casing - Diameter</label>
                            <input type="number" name="details[0][cases][0][actual_case_2]" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label>Standar Berat (gr)</label>
                            <input type="number" step="0.01" name="details[0][weight_standard]" class="form-control"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>Standar Panjang</label>
                            <input type="number" step="0.01" name="details[0][long_standard]" class="form-control"
                                required>
                        </div>
                    </div>

                    <!-- <div class="row g-3 mb-3">

                        <div class="col-md-3">
                            <label>Berat 1</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_weight_1]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Berat 2</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_weight_2]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Berat 3</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_weight_3]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Rata-rata Berat</label>
                            <input type="number" step="0.01" name="details[0][avg_weight]" class="form-control"
                                readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">

                        <div class="col-md-3">
                            <label>Panjang 1</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_long_1]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Panjang 2</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_long_2]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Panjang 3</label>
                            <input type="number" step="0.01" name="details[0][weights][0][actual_long_3]"
                                class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Rata-rata Panjang</label>
                            <input type="number" step="0.01" name="details[0][avg_long]" class="form-control" readonly>
                        </div>
                    </div> -->

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Berat Aktual (gr)</label>
                            <div id="weightWrapper-0" class="row g-2">
                                @for($i = 1; $i <= 3; $i++) <div class="col-md-4">
                                    <label>Berat {{ $i }}</label>
                                    <input type="number" step="0.01"
                                        name="details[0][weights][0][actual_weight_{{ $i }}]" class="form-control">
                            </div>
                            @endfor
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2 add-weight" data-index="0">
                            + Tambah Berat
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Rata-rata Berat</label>
                        <input type="number" step="0.01" name="details[0][avg_weight]" class="form-control" readonly>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Panjang Aktual</label>
                            <div id="longWrapper-0" class="row g-2">
                                @for($i = 1; $i <= 3; $i++) <div class="col-md-4">
                                    <label>Panjang {{ $i }}</label>
                                    <input type="number" step="0.01" name="details[0][weights][0][actual_long_{{ $i }}]"
                                        class="form-control">
                            </div>
                            @endfor
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2 add-long" data-index="0">
                            + Tambah Panjang
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Rata-rata Panjang</label>
                        <input type="number" step="0.01" name="details[0][avg_long]" class="form-control" readonly>
                    </div>

                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-success float-end">Simpan Detail</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
// let index = 1;
// const template = document.querySelector('.detail-block');

// document.getElementById('addProductDetail')?.addEventListener('click', function() {
//     const clone = template.cloneNode(true);
//     clone.querySelectorAll('input, select, textarea').forEach(el => {
//         if (el.name) el.name = el.name.replace(/\[0\]/g, `[${index}]`);
//         if (el.type !== 'hidden') el.value = '';
//     });
//     document.getElementById('productDetails').appendChild(clone);
//     index++;
// });

// document.addEventListener('click', function(e) {
//     if (e.target.classList.contains('remove-detail')) {
//         const block = e.target.closest('.detail-block');
//         if (document.querySelectorAll('.detail-block').length > 1) {
//             block.remove();
//         }
//     }
// });

// document.addEventListener('change', function(e) {
//     if (e.target.matches('.product-select')) {
//         const option = e.target.selectedOptions[0];
//         const cardBody = e.target.closest('.card-body');

//         if (option && cardBody) {
//             const weightStandard = option.dataset.weightStandard;
//             const diameter = option.dataset.diameter;
//             const longMax = option.dataset.longMax;

//             const weightStandardInput = cardBody.querySelector('input[name$="[weight_standard]"]');
//             if (weightStandard) {
//                 weightStandardInput.value = weightStandard;
//             } else {
//                 weightStandardInput.value = '';
//             }

//             cardBody.querySelectorAll('input[name*="[actual_case_1]"]').forEach(input => {
//                 input.value = longMax || '';
//             });
//             cardBody.querySelectorAll('input[name*="[actual_case_2]"]').forEach(input => {
//                 input.value = diameter || '';
//             });

//             // Reset field lain
//             cardBody.querySelectorAll('input').forEach(input => {
//                 const name = input.name;
//                 if (name.endsWith('[weight_standard]')) return;
//                 if (name.includes('[actual_case_1]')) return;
//                 if (name.includes('[actual_case_2]')) return;
//                 input.value = '';
//             });
//         }
//     }
// });

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