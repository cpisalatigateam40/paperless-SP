@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_retain_samples.store') }}" method="POST">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-header">
                <h4 class="mb-3">Tambah Laporan Retain Sample</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <hr>
                <h6>Detail Produk Retain Sample</h6>
                <div id="detail-wrapper">
                    <div class="border rounded p-3 mb-3 detail-row">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label>ABF/IQF</label>
                                <select name="details[0][line_type]" class="form-select form-control">
                                    <option value="">Pilih</option>
                                    <option value="ABF">ABF</option>
                                    <option value="IQF">IQF</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Jam Masuk</label>
                                <input type="time" name="details[0][time_in]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Suhu Room (°C)</label>
                                <input type="number" step="0.01" name="details[0][room_temp]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Suction (°C)</label>
                                <input type="number" step="0.01" name="details[0][suction_temp]" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Speed Display</label>
                                <input type="number" step="0.01" name="details[0][display_speed]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Speed Aktual</label>
                                <input type="number" step="0.01" name="details[0][actual_speed]" class="form-control">
                            </div>
                        </div>

                        <div id="product-fields-wrapper">
                            <div class="row mb-3 product-field">
                                <div class="col-md-6">
                                    <label>Nama Produk</label>
                                    <select name="details[0][product_uuid]" class="form-select form-control" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->uuid }}">{{ $product->product_name }}
                                            {{ $product->nett_weight }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Kode Produksi</label>
                                    <input type="text" name="details[0][production_code]" class="form-control">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProductField()">Tambah
                            Produk</button>


                        <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button>


                    </div>
                </div>


                <button type="button" id="add-detail" class="btn btn-sm btn-success mt-2">+ Tambah Detail</button>
                <hr>
                <button type="submit" class="btn btn-primary">Simpan Laporan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let index = 1;

document.getElementById('add-detail').addEventListener('click', function() {
    let wrapper = document.getElementById('detail-wrapper');
    let row = wrapper.querySelector('.detail-row').cloneNode(true);

    row.querySelectorAll('input, select').forEach(el => {
        const name = el.getAttribute('name');
        if (name) {
            el.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
        }
        el.value = '';
    });

    wrapper.appendChild(row);
    index++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        const rows = document.querySelectorAll('.detail-row');
        if (rows.length > 1) {
            e.target.closest('.detail-row').remove();
        }
    }
});

let productIndex = 1; // Mulai dari 1 karena sudah ada detail[0]

function addProductField() {
    // Dapatkan container utama
    const wrapper = document.getElementById('product-fields-wrapper');

    // Buat elemen baru (copy dari template)
    const newField = document.createElement('div');
    newField.classList.add('row', 'mb-3', 'product-field');
    newField.innerHTML = `
        <div class="col-md-6">
            <label>Nama Produk</label>
            <select name="details[${productIndex}][product_uuid]" class="form-select form-control" required>
                <option value="">Pilih Produk</option>
                @foreach ($products as $product)
                <option value="{{ $product->uuid }}">{{ $product->product_name }} {{ $product->nett_weight }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label>Kode Produksi</label>
            <input type="text" name="details[${productIndex}][production_code]" class="form-control">
        </div>
    `;

    // Tambahkan ke wrapper
    wrapper.appendChild(newField);

    // Tambahkan index untuk field berikutnya
    productIndex++;
}
</script>
@endsection