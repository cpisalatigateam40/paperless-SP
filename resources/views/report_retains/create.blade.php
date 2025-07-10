@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Retained Sample Report</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_retains.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}"
                        required>
                </div>

                <div class="mb-2">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control" required>
                        <option value="">-- Pilih Section --</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label>Sampel Storage</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Frozen (≤ -18°C)">
                        <label class="form-check-label">Frozen (≤ -18°C)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Chilled (0 - 5°C)">
                        <label class="form-check-label">Chilled (0 - 5°C)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Other">
                        <label class="form-check-label">Other</label>
                    </div>
                </div>

                <hr>
                <h5 class="mb-4 mt-5">Detail Produk</h5>
                <div id="details-container">
                    <div class="detail-item mb-3 border p-2">
                        <label>Nama Produk</label>
                        <select name="details[0][product_uuid]" class="form-control"
                            onchange="updateExpiredDate(this, 0)" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                                data-created-at="{{ $product->created_at }}">
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>

                        <label>Kode Produksi</label>
                        <input type="text" name="details[0][production_code]" class="form-control">

                        <label>Best Before</label>
                        <input type="date" name="details[0][best_before]" class="form-control" readonly>

                        <label>Jumlah</label>
                        <input type="number" name="details[0][quantity]" class="form-control">

                        <label>Catatan</label>
                        <input type="text" name="details[0][notes]" class="form-control">
                    </div>
                </div>

                <button type="button" onclick="addDetail()" class="btn btn-sm btn-secondary mb-2">+ Tambah
                    Detail</button>
                <br>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>

<script>
let detailIndex = 1;

function addDetail() {
    let container = document.getElementById('details-container');
    let html = `
        <div class="detail-item mb-3 border p-2">
            <label>Nama Produk</label>
            <select name="details[${detailIndex}][product_uuid]" class="form-control" onchange="updateExpiredDate(this, ${detailIndex})" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $product)
                    <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}" data-created-at="{{ $product->created_at }}">
                        {{ $product->product_name }}
                    </option>
                @endforeach
            </select>

            <label>Kode Produksi</label>
            <input type="text" name="details[${detailIndex}][production_code]" class="form-control">

            <label>Best Before</label>
            <input type="date" name="details[${detailIndex}][best_before]" class="form-control" readonly>

            <label>Jumlah</label>
            <input type="number" name="details[${detailIndex}][quantity]" class="form-control">

            <label>Catatan</label>
            <input type="text" name="details[${detailIndex}][notes]" class="form-control">
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    detailIndex++;
}

function updateExpiredDate(select, index) {
    let selectedOption = select.options[select.selectedIndex];
    let shelfLife = selectedOption.getAttribute('data-shelf-life');
    let createdAt = selectedOption.getAttribute('data-created-at');
    if (shelfLife && createdAt) {
        let createdDate = new Date(createdAt);
        createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

        let year = createdDate.getFullYear();
        let month = String(createdDate.getMonth() + 1).padStart(2, '0');
        let day = String(createdDate.getDate()).padStart(2, '0');

        let expiredDateStr = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][best_before]"]`).value = expiredDateStr;
    }
}
</script>
@endsection