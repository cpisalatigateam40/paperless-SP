@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail ke Report Tanggal {{ $report->date }} (Shift {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_lab_samples.details.store', $report->uuid) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nama Produk</label>
                        <select name="product_uuid" class="form-control" required onchange="updateExpiredDate(this)">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                                data-created-at="{{ $product->created_at }}">
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gramase</label>
                        <input type="number" step="0.01" name="gramase" class="form-control"
                            placeholder="Masukkan gramase" required>
                    </div>
                </div>

                <div class="row detail-row">
                    <div class="col-md-6 mb-3">
                        <label>Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control production-code" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Best Before</label>
                        <input type="date" name="best_before" class="form-control best-before" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>Jenis Sample</label>
                        <select name="sample_type" class="form-control mb-3" required>
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
                        <input type="number" name="quantity" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Satuan</label>
                        <select name="unit" class="form-control mb-3" required>
                            <option value="">-- Pilih Satuan --</option>
                            <option value="pcs">pcs</option>
                            <option value="pack">pack</option>
                            <option value="inner">inner</option>
                            <option value="box">box</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>

<script>
function updateExpiredDate(select) {
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
        document.querySelector('input[name="best_before"]').value = expiredDateStr;
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