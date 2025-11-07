@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Lab Sample</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_lab_samples.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}"
                        required>
                </div>

                <div class="mb-2">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}"
                        required>
                </div>

                <div class="mb-2">
                    <label>Sampel Storage</label><br>
                    @php
                    $storages = explode(', ', $report->storage ?? '');
                    @endphp
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Frozen (≤ -18°C)"
                            {{ in_array('Frozen (≤ -18°C)', $storages) ? 'checked' : '' }}>
                        <label class="form-check-label">Frozen (≤ -18°C)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Chilled (0 - 5°C)"
                            {{ in_array('Chilled (0 - 5°C)', $storages) ? 'checked' : '' }}>
                        <label class="form-check-label">Chilled (0 - 5°C)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="storage[]" value="Other"
                            {{ in_array('Other', $storages) ? 'checked' : '' }}>
                        <label class="form-check-label">Other</label>
                    </div>
                </div>

                <hr>
                <h5 class="mb-4 mt-5">Detail Produk</h5>
                <div id="details-container">
                    @foreach ($report->details as $i => $detail)
                    <div class="detail-item mb-3 border p-2">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Nama Produk</label>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control select2 mb-3"
                                    required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        {{ $product->uuid == $detail->product_uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gramase</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][gramase]" class="form-control"
                                    value="{{ $detail->gramase }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control mb-3"
                                    value="{{ $detail->production_code }}">
                            </div>

                            <div class="col-md-6">
                                <label>Best Before</label>
                                <input type="date" name="details[{{ $i }}][best_before]" class="form-control mb-3"
                                    value="{{ $detail->best_before }}">
                            </div>
                        </div>

                        <label>Jumlah</label>
                        <input type="number" name="details[{{ $i }}][quantity]" class="form-control mb-3"
                            value="{{ $detail->quantity }}">

                        <label>Catatan</label>
                        <input type="text" name="details[{{ $i }}][notes]" class="form-control mb-3"
                            value="{{ $detail->notes }}">
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addDetail()" class="btn btn-sm btn-secondary mb-2">+ Tambah
                    Detail</button>
                <br>
                <div class="text-end">
                    <a href="{{ route('report_lab_samples.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
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
            <div class="row">
                <div class="col-md-6">
                    <label>Nama Produk</label>
                    <select name="details[${detailIndex}][product_uuid]" id="product-select" class="form-control select2 mb-3"
                        required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}" data-name="{{ $product->product_name }}">
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gramase</label>
                        <input type="number" step="0.01" name="details[${detailIndex}][gramase]" class="form-control" placeholder="Masukkan gramase" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Kode Produksi</label>
                    <input type="text" name="details[${detailIndex}][production_code]" class="form-control mb-3">
                </div>

                <div class="col-md-6">
                    <label>Best Before</label>
                    <input type="date" name="details[${detailIndex}][best_before]" class="form-control mb-3">
                </div>
            </div>

            <label>Jumlah</label>
            <input type="number" name="details[${detailIndex}][quantity]" class="form-control mb-3">

            <label>Catatan</label>
            <input type="text" name="details[${detailIndex}][notes]" class="form-control mb-3">
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
        let month = String(createdDate.getMonth() + 1).padStart(2, '0'); // Month: 0-indexed
        let day = String(createdDate.getDate()).padStart(2, '0');

        let expiredDateStr = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][best_before]"]`).value = expiredDateStr;
    }
}
</script>
@endsection