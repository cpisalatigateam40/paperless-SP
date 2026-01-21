@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_tofu_verifs.store') }}" method="POST">
        @csrf

        {{-- HEADER --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Tambah Laporan Verifikasi Produk Tofu</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control">
                </div>
            </div>
        </div>

        {{-- PRODUK ACCORDION --}}
        <!-- <div class="accordion" id="produkAccordion">
            @for ($i = 0; $i < 4; $i++) <div class="accordion-item mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-primary text-white" type="button"
                        data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}">
                        Produk #{{ $i + 1 }}
                    </button>
                </h2>
                <div id="produk{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#produkAccordion">

                </div>
        </div>
        @endfor -->

        <div class="accordion-body card shadow">
            <div class="card-body g-3">
                <div class="detail-row row">
                    <div class="col-md-4">
                        <label>Kode Produksi</label>
                        <input type="text" name="products[{{ $i }}][production_code]" class="form-control production-code">
                    </div>
                    <div class="col-md-4">
                        <label>Best Before</label>
                        <input type="date" name="products[{{ $i }}][expired_date]" class="form-control best-before" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Jumlah Sampel (pcs)</label>
                        <input type="number" name="products[{{ $i }}][sample_amount]" class="form-control">
                    </div>
                </div>
            </div>

            <hr class="my-3">
            <h6 style="margin-left: 2rem;"><strong>Hasil Pemeriksaan Berat</strong></h6>
            @php
            $weights = ['under' => 'Under (< 11gr)', 'standard'=> 'Standard (11 - 13gr)', 'over' => 'Over (>
                13gr)'];
                @endphp
                @foreach ($weights as $key => $label)
                <div class="row mb-2 card-body">
                    <div class="col-md-4">
                        <label>{{ $label }} - Turus</label>
                        <input type="number" name="products[{{ $i }}][weight_verifs][{{ $key }}][turus]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - Jumlah</label>
                        <input type="number" name="products[{{ $i }}][weight_verifs][{{ $key }}][total]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - %</label>
                        <input type="number" step="0.01"
                            name="products[{{ $i }}][weight_verifs][{{ $key }}][percentage]" class="form-control">
                    </div>
                </div>
                @endforeach

                <hr class="my-3">
                <h6 style="margin-left: 2rem;"><strong>Hasil Pemeriksaan Defect</strong></h6>
                @php
                $defects = ['hole' => 'Berlubang', 'stain' => 'Noda', 'asymmetry' => 'Tidak Bulat Simetris',
                'other' => 'Lain-lain', 'good' => 'Produk Bagus', 'note' => 'Keterangan'];
                @endphp
                @foreach ($defects as $key => $label)
                <div class="row mb-2 card-body">
                    <div class="col-md-4">
                        <label>{{ $label }} - Turus</label>
                        <input type="number" name="products[{{ $i }}][defect_verifs][{{ $key }}][turus]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - Jumlah</label>
                        <input type="number" name="products[{{ $i }}][defect_verifs][{{ $key }}][total]"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $label }} - %</label>
                        <input type="number" step="0.01"
                            name="products[{{ $i }}][defect_verifs][{{ $key }}][percentage]" class="form-control">
                    </div>
                </div>
                @endforeach
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">Simpan Laporan</button>
        </div>
</div>


</form>
</div>

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