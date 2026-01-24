@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Tambah Laporan Verifikasi Metal Detector Produk</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_md_products.store') }}">
                @csrf

                {{-- HEADER REPORT --}}
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>
                <hr>

                <p class="mt-5">Pilih Tipe</p>
                <div class="d-flex " style="gap: 2rem;">
                    <label class="me-3">
                        <input type="radio" name="details[0][process_type]" value="Manual">
                        Manual
                    </label>

                    <label class="me-3">
                        <input type="radio" name="details[0][process_type]" value="CFS">
                        CFS
                    </label>

                    <label class="me-3">
                        <input type="radio" name="details[0][process_type]" value="Colimatic">
                        Colimatic
                    </label>

                    <label class="me-3">
                        <input type="radio" name="details[0][process_type]" value="Multivac">
                        Multivac
                    </label>
                </div>

                <h5 class="mt-5">Detail Pemeriksaan</h5>

                {{-- DETAIL --}}
                <div class="mb-3">
                    <label>Waktu Pengecekan</label>
                    <input type="time" name="details[0][time]" class="form-control"
                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                </div>
                <div class=" mb-3">
                    <label>Nama Produk</label>
                    <select name="details[0][product_uuid]" class="form-control select2-product"
                        onchange="updateBestBefore(this, 0)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                            data-created-at="{{ $product->created_at }}">
                            {{ $product->product_name }} - {{ $product->nett_weight }} g
                        </option>
                        @endforeach
                    </select>
                </div>
                <!-- <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="details[0][production_code]" class="form-control production-code">
                </div>
                <div class="mb-3">
                    <label>Best Before</label>
                    <input type="date" name="details[0][best_before]" class="form-control best-before">
                </div> -->
                <div class="detail-row">
                    <div class="mb-3">
                        <label>Kode Produksi</label>
                        <input type="text" name="details[0][production_code]" class="form-control production-code">
                    </div>
                    <div class="mb-3">
                        <label>Best Before</label>
                        <input type="date" name="details[0][best_before]" class="form-control best-before" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Nomor Program</label>
                    <input type="text" name="details[0][program_number]" class="form-control">
                </div>

                <h6 class="mt-4">Hasil Pemeriksaan Verifikasi Specimen</h6>

                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Specimen</th>
                            <th>Depan (D)</th>
                            <th>Tengah (T)</th>
                            <th>Belakang (B)</th>
                            <th>Dalam Tumpukan (DL)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $specimens = ['fe_1_5mm' => 'Fe 1.5 mm', 'non_fe_2mm' => 'Non Fe 2 mm', 'sus_2_5mm' => 'SUS 2.5
                        mm'];
                        $positions = ['d', 't', 'b', 'dl'];
                        $posIdx = 0;
                        @endphp
                        @foreach ($specimens as $specimenKey => $specimenName)
                        <tr>
                            <td>{{ $specimenName }}</td>
                            @foreach ($positions as $posKey)
                            <td>
                                <input type="hidden" name="details[0][positions][{{ $posIdx }}][specimen]"
                                    value="{{ $specimenKey }}">
                                <input type="hidden" name="details[0][positions][{{ $posIdx }}][position]"
                                    value="{{ $posKey }}">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                        name="details[0][positions][{{ $posIdx }}][status]" value="1" checked>
                                    <label class="form-check-label">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                        name="details[0][positions][{{ $posIdx }}][status]" value="0">
                                    <label class="form-check-label">Tidak OK</label>
                                </div>
                            </td>
                            @php $posIdx++; @endphp
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row">
                    <div class="mb-3 mt-3 col-md-6">
                        <label>Tindakan Perbaikan</label>
                        <input type="text" name="details[0][corrective_action]" class="form-control">
                    </div>
                    <div class="mb-3 mt-3 col-md-6">
                        <label>Verifikasi Setelah Perbaikan</label>
                        <select name="details[0][verification]" class="form-control">
                            <option value="">-- Pilih Verifikasi --</option>
                            <option value="0">Tidak OK</option>
                            <option value="1">OK</option>
                        </select>
                    </div>
                </div>

                <div id="details-wrapper">
                    {{-- detail pertama di sini --}}
                </div>

                <button type="button" class="btn btn-primary" onclick="addDetail()">+ Tambah Detail</button>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('report_md_products.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1; // detail pertama sudah [0]

function addDetail() {
    let html = `

    <p class="mt-5">Pilih Tipe</p>
    <div class="d-flex " style="gap: 2rem;">
        <label class="me-3">
            <input type="radio" name="details[${detailIndex}][process_type]" value="Manual">
            Manual
        </label>

        <label class="me-3">
            <input type="radio" name="details[${detailIndex}][process_type]" value="CFS">
            CFS
        </label>

        <label class="me-3">
            <input type="radio" name="details[${detailIndex}][process_type]" value="Colimatic">
            Colimatic
        </label>

        <label class="me-3">
            <input type="radio" name="details[${detailIndex}][process_type]" value="Multivac">
            Multivac
        </label>
    </div>
    <div class="border rounded p-3 mb-3 mt-5">
        <div class="mb-3">
            <label>Waktu Pengecekan</label>
            <input type="time" name="details[${detailIndex}][time]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
        </div>
        <div class="mb-3">
            <label>Nama Produk</label>
            <select name="details[${detailIndex}][product_uuid]" class="form-control select2-product"
                    onchange="updateBestBefore(this, ${detailIndex})">
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->uuid }}"
                            data-shelf-life="{{ $product->shelf_life }}"
                            data-created-at="{{ $product->created_at }}">
                        {{ $product->product_name }} - {{ $product->nett_weight }} g
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="detail-row">
            <div class="mb-3">
                <label>Kode Produksi</label>
                <input type="text" name="details[${detailIndex}][production_code]" class="form-control production-code">
            </div>
            <div class="mb-3">
                <label>Best Before</label>
                <input type="date" name="details[${detailIndex}][best_before]" class="form-control best-before" readonly>
            </div>
        </div>
        <div class="mb-3">
            <label>Nomor Program</label>
            <input type="text" name="details[${detailIndex}][program_number]" class="form-control">
        </div>

        <h6>Hasil Pemeriksaan Verifikasi Specimen</h6>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Specimen</th>
                    <th>Depan (D)</th>
                    <th>Tengah (T)</th>
                    <th>Belakang (B)</th>
                    <th>Dalam Tumpukan (DL)</th>
                </tr>
            </thead>
            <tbody>
                @php
                $specimens = ['fe_1_5mm' => 'Fe 1.5 mm', 'non_fe_2mm' => 'Non Fe 2 mm', 'sus_2_5mm' => 'SUS 2.5 mm'];
                $positions = ['d', 't', 'b', 'dl'];
                @endphp
                @foreach ($specimens as $specimenKey => $specimenName)
                <tr>
                    <td>{{ $specimenName }}</td>
                    @foreach ($positions as $posKey)
                        <td>
                            <input type="hidden" name="details[${detailIndex}][positions][{{$loop->parent->index*4 + $loop->index}}][specimen]" value="{{ $specimenKey }}">
                            <input type="hidden" name="details[${detailIndex}][positions][{{$loop->parent->index*4 + $loop->index}}][position]" value="{{ $posKey }}">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="details[${detailIndex}][positions][{{$loop->parent->index*4 + $loop->index}}][status]" value="1" checked>
                                <label class="form-check-label">OK</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="details[${detailIndex}][positions][{{$loop->parent->index*4 + $loop->index}}][status]" value="0">
                                <label class="form-check-label">Tidak OK</label>
                            </div>
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row">
            <div class="mb-3 col-md-6 mt-3">
                <label>Tindakan Perbaikan</label>
                <input type="text" name="details[${detailIndex}][corrective_action]" class="form-control">
            </div>
            <div class="mb-3 col-md-6 mt-3">
                <label>Verifikasi Setelah Perbaikan</label>
                <select name="details[${detailIndex}][verification]" class="form-control">
                    <option value="">-- Pilih Verifikasi --</option>
                    <option value="0">Tidak OK</option>
                    <option value="1">OK</option>
                </select>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="btn btn-sm btn-danger">Hapus Detail</button>
    </div>
    `;
    document.getElementById('details-wrapper').insertAdjacentHTML('beforeend', html);
    detailIndex++;
}

function updateBestBefore(select, index) {
    let option = select.options[select.selectedIndex];
    let shelfLife = option.getAttribute('data-shelf-life');
    let createdAt = option.getAttribute('data-created-at');
    if (shelfLife && createdAt) {
        let createdDate = new Date(createdAt);
        createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

        let year = createdDate.getFullYear();
        let month = String(createdDate.getMonth() + 1).padStart(2, '0');
        let day = String(createdDate.getDate()).padStart(2, '0');

        let bestBeforeStr = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][best_before]"]`).value = bestBeforeStr;
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