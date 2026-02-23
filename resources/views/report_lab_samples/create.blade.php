@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Laporan Verifikasi Lab Sample</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_lab_samples.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="mb-2 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}"
                            required>
                    </div>
                    <div class="mb-2 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>
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
                        <div class="row">
                            <div class="col-md-6">
                                <label>Nama Produk</label>
                                <select name="details[0][product_uuid]" id="product-select"
                                    class="form-control select2 mb-3" required>
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
                                <input type="number" step="0.01" name="details[0][gramase]" class="form-control"
                                    placeholder="Masukkan gramase" required>
                            </div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-6">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control mb-3 production-code">
                            </div>

                            <div class="col-md-6">
                                <label>Best Before</label>
                                <input type="date" name="details[0][best_before]" class="form-control mb-3 best-before" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Jenis Sample</label>
                                <select name="details[0][sample_type]" class="form-control mb-3" required>
                                    <option value="">-- Pilih Jenis Sample --</option>
                                    <option value="Sampel Lab">Sampel Lab</option>
                                    <option value="Sampel Retained">Sampel Retained</option>
                                    <option value="Sampel RnD">Sampel RnD</option>
                                    <option value="Sampel Trial">Sampel Trial</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Jumlah</label>
                                <input type="number" name="details[0][quantity]" class="form-control mb-3">
                            </div>
                            <div class="col-md-6">
                                <label>Satuan</label>
                                <select name="details[0][unit]" class="form-control mb-3" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    <option value="pcs">pcs</option>
                                    <option value="pack">pack</option>
                                    <option value="inner">inner</option>
                                    <option value="box">box</option>
                                </select>
                            </div>
                        </div>

                        <label>Catatan</label>
                        <input type="text" name="details[0][notes]" class="form-control mb-3">
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

            <div class="row detail-row">
                <div class="col-md-6">
                    <label>Kode Produksi</label>
                    <input type="text" name="details[${detailIndex}][production_code]" class="form-control mb-3 production-code">
                </div>

                <div class="col-md-6">
                    <label>Best Before</label>
                    <input type="date" name="details[${detailIndex}][best_before]" class="form-control mb-3 best-before" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Jenis Sample</label>
                    <select name="details[${detailIndex}][sample_type]" class="form-control mb-3">
                        <option value="">-- Pilih Jenis Sample --</option>
                        <option value="Sampel Lab">Sampel Lab</option>
                        <option value="Sampel Retained">Sampel Retained</option>
                        <option value="Sampel RnD">Sampel RnD</option>
                        <option value="Sampel Trial">Sampel Trial</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Jumlah</label>
                    <input type="number" name="details[${detailIndex}][quantity]" class="form-control mb-3">
                </div>
                <div class="col-md-6">
                    <label>Satuan</label>
                    <select name="details[${detailIndex}][unit]" class="form-control mb-3">
                        <option value="">-- Pilih Satuan --</option>
                        <option value="pcs">pcs</option>
                        <option value="pack">pack</option>
                        <option value="inner">inner</option>
                        <option value="box">box</option>
                    </select>
                </div>
            </div>

            

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

<script>
function formatDateLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseBatchCodeToDate(batchCode) {
    if (!batchCode || batchCode.length < 4) {
        return null;
    }

    try {
        const yearChar = batchCode[0].toUpperCase();
        const baseYear = 2009;
        const year = baseYear + (yearChar.charCodeAt(0) - 'A'.charCodeAt(0));

        const monthChar = batchCode[1].toUpperCase();
        const month = (monthChar.charCodeAt(0) - 'A'.charCodeAt(0)) + 1;

        const day = parseInt(batchCode.substring(2, 4), 10);

        if (
            isNaN(year) ||
            isNaN(month) || month < 1 || month > 12 ||
            isNaN(day) || day < 1 || day > 31
        ) {
            return null;
        }

        return new Date(year, month - 1, day);
    } catch (e) {
        return null;
    }
}

function calculateExpirationDate(batchCode, expirationMonths) {
    const productionDate = parseBatchCodeToDate(batchCode);

    if (!productionDate || isNaN(expirationMonths)) {
        return null;
    }

    const originalDay = productionDate.getDate();

    let expirationDate = new Date(
        productionDate.getFullYear(),
        productionDate.getMonth(),
        originalDay
    );

    expirationDate.setMonth(expirationDate.getMonth() + expirationMonths);

    const lastDayOfNewMonth = new Date(
        expirationDate.getFullYear(),
        expirationDate.getMonth() + 1,
        0
    ).getDate();

    expirationDate.setDate(Math.min(originalDay, lastDayOfNewMonth));

    return {
        production_date: formatDateLocal(productionDate),
        expiration_date: formatDateLocal(expirationDate)
    };
}


document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('production-code')) return;

    const row = e.target.closest('.detail-row');
    const bestBeforeInput = row.querySelector('.best-before');

    // ambil QA01
    const match = e.target.value.match(/([A-Z]{2}\d{2})/i);
    if (!match) {
        bestBeforeInput.value = '';
        return;
    }

    const batchCode = match[1].toUpperCase();
    const expirationMonths = 24;

    const result = calculateExpirationDate(batchCode, expirationMonths);
    bestBeforeInput.value = result ? result.expiration_date : '';
});
</script>
@endsection