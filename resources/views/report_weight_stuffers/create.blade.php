@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.store') }}" method="POST">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-header">Header Laporan</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-2">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                </div>
            </div>
        </div>

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
                            <select name="details[0][product_uuid]" class="form-control" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
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
                                    <th>Aktual Panjang</th>
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

                                    {{-- Weight Standard hanya di baris pertama --}}
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
            <button type="submit" class="btn btn-success float-end">Simpan Laporan</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let index = 1;
const template = document.querySelector('.detail-block');

document.getElementById('addProductDetail').addEventListener('click', function() {
    const clone = template.cloneNode(true);

    clone.querySelectorAll('input, select, textarea').forEach(el => {
        if (!el.name) return;

        // Ganti semua [0] → [index] untuk field utama
        el.name = el.name.replace(/\[0\]/g, `[${index}]`);

        // Kosongkan nilai input
        if (el.type !== 'hidden') el.value = '';
    });

    // Ganti nested index khusus baris Hitech (baris ke-2 tbody)
    const rows = clone.querySelectorAll('tbody tr');
    if (rows.length >= 2) {
        const hitechInputs = rows[1].querySelectorAll('input, select, textarea');
        hitechInputs.forEach(input => {
            if (!input.name) return;

            input.name = input.name.replace(`[${index}][weights][0]`, `[${index}][weights][1]`);
            input.name = input.name.replace(`[${index}][cases][0]`, `[${index}][cases][1]`);
        });
    }

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
</script>
@endsection