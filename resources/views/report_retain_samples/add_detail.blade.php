@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_retain_samples.store-detail', $report->uuid) }}" method="POST">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-header">
                <h4 class="mb-3">Tambah Detail Laporan Retain Sample</h4>
                <div class="d-flex" style="gap: 4rem;">
                    <p>Tanggal: {{ $report->date }}</p>
                    <p>Shift: {{ $report->shift }}</p>
                    <p>Dibuat oleh: {{ $report->created_by }}</p>
                </div>
            </div>
            <div class="card-body">
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

                        <div class="product-wrapper">
                            <div class="row mb-3 product-row">
                                <div class="col-md-6">
                                    <label>Nama Produk</label>
                                    <select name="details[0][products][0][product_uuid]"
                                        class="form-select form-control" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Kode Produksi</label>
                                    <input type="text" name="details[0][products][0][production_code]"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm add-product">Tambah Produk</button>
                        <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button>
                    </div>
                </div>

                <button type="button" id="add-detail" class="btn btn-sm btn-success mt-2">+ Tambah Detail</button>
                <hr>
                <button type="submit" class="btn btn-primary">Simpan Detail</button>
                <a href="{{ route('report_retain_samples.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;

// Tambah detail baru
document.getElementById('add-detail').addEventListener('click', function() {
    let wrapper = document.getElementById('detail-wrapper');
    let template = wrapper.querySelector('.detail-row');
    let newRow = template.cloneNode(true);

    // Ganti semua name: details[0] → details[detailIndex]
    newRow.querySelectorAll('input, select').forEach(el => {
        if (el.name) {
            el.name = el.name.replace(/details\[\d+\]/, `details[${detailIndex}]`);
        }
        el.value = '';
    });

    wrapper.appendChild(newRow);
    detailIndex++;
});

// Hapus detail
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        const rows = document.querySelectorAll('.detail-row');
        if (rows.length > 1) {
            e.target.closest('.detail-row').remove();
        }
    }
});

// Tambah produk dalam satu detail
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-product')) {
        let detailRow = e.target.closest('.detail-row');
        let productWrapper = detailRow.querySelector('.product-wrapper');
        let productRows = productWrapper.querySelectorAll('.product-row');
        let productIndex = productRows.length;

        // Ambil detailIndex
        let firstSelect = productRows[0].querySelector('select');
        let detailIndexMatch = firstSelect.name.match(/details\[(\d+)\]/);
        let detailIndex = detailIndexMatch ? detailIndexMatch[1] : '0';

        let newProductRow = productRows[0].cloneNode(true);

        newProductRow.querySelectorAll('select, input').forEach(el => {
            const field = el.name.includes('product_uuid') ? 'product_uuid' : 'production_code';
            el.name = `details[${detailIndex}][products][${productIndex}][${field}]`;
            el.value = '';
        });

        productWrapper.appendChild(newProductRow);
    }
});
</script>
@endsection