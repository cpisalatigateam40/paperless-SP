@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Tambah Detail Laporan Verifikasi Pasteurisasi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_pasteurs.store_detail', $report->uuid) }}" method="POST" id="pasteur-form"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="report_uuid" value="{{ $report->uuid }}">

                <h5 class="mt-4">Detail Produk</h5>
                <div id="detail-wrapper">
                    <!-- Template detail item -->
                    <div class="border p-3 mb-3 detail-item d-none" id="detail-template">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Produk</label>
                                <select class="form-control product-select select2-product">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }} -
                                        {{ $product->nett_weight }} g</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Nomor Program</label>
                                <input type="text" class="form-control program-number">
                            </div>

                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Kode Produk</label>
                                <input type="text" class="form-control product-code">
                            </div>
                            <div class="col-md-6">
                                <label>Kemasan (gr)</label>
                                <input type="number" class="form-control for-packaging-gr">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Jumlah Troly</label>
                                <input type="number" class="form-control trolley-count">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.1" class="form-control product-temp">
                            </div>
                        </div>

                        {{-- Steps --}}
                        <h6 class="mt-4">Steps</h6>
                        @php
                        $standardSteps = [
                        1 => 'Water Injection',
                        2 => 'Up Temperature',
                        3 => 'Pasteurisasi',
                        4 => 'Hot Water Recycling',
                        5 => 'Cooling Water Injection',
                        6 => 'Cooling Constant Temp.',
                        7 => 'Raw Cooling Water'
                        ];
                        @endphp
                        <div class="steps-wrapper">
                            @foreach($standardSteps as $order => $name)
                            <div class="p-2 border mb-4 step-item">
                                <strong>{{ $order }}. {{ $name }}</strong>

                                {{-- Hidden step info --}}
                                <input type="hidden" class="step-name" value="{{ $name }}">
                                <input type="hidden" class="step-order" value="{{ $order }}">
                                <input type="hidden" class="step-type" value="standard">

                                <div class="row mb-4 mt-2">
                                    <div class="col-md-3">
                                        <label>Jam Mulai</label>
                                        <input type="time" class="form-control start-time">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Jam Selesai</label>
                                        <input type="time" class="form-control end-time">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Suhu Air (°C)</label>
                                        <input type="number" step="0.1" class="form-control water-temp">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Tekanan (Bar)</label>
                                        <input type="number" step="0.01" class="form-control pressure">
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            {{-- Step 8: Drainage --}}
                            <div class="p-2 border mb-4 step-item">
                                <strong>Step 8: Drainage Pressure</strong>
                                <input type="hidden" class="step-name" value="Drainage">
                                <input type="hidden" class="step-order" value="8">
                                <input type="hidden" class="step-type" value="drainage">

                                <div class="row mb-4 mt-2">
                                    <div class="col-md-6">
                                        <label>Jam Mulai</label>
                                        <input type="time" class="form-control start-time">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Jam Selesai</label>
                                        <input type="time" class="form-control end-time">
                                    </div>
                                </div>
                            </div>

                            {{-- Step 9: Finish --}}
                            <div class="p-2 border mb-4 step-item">
                                <strong>Step 9: Finish Produk</strong>
                                <input type="hidden" class="step-name" value="Finish">
                                <input type="hidden" class="step-order" value="9">
                                <input type="hidden" class="step-type" value="finish">

                                <div class="row mb-4 mt-2">
                                    <div class="col-md-6">
                                        <label>Suhu Inti Produk (°C)</label>
                                        <input type="number" step="0.1" class="form-control product-core-temp">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Sortasi</label>
                                        <input type="text" class="form-control sortation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Paraf --}}
                        <h6 class="mt-4">Paraf</h6>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label">Paraf QC</label>
                                <canvas class="border qc-canvas" width="300" height="150"></canvas>
                                <input type="hidden" class="qc-input">
                                <button type="button"
                                    class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paraf Produksi</label>
                                <canvas class="border prod-canvas" width="300" height="150"></canvas>
                                <input type="hidden" class="prod-input">
                                <button type="button"
                                    class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-detail d-none">Hapus
                            Detail</button>
                    </div>
                </div>

                <button type="button" class="btn btn-primary mt-3 d-none" id="add-detail">Tambah Detail</button>
                <button type="submit" class="btn btn-success mt-3">Simpan Detail</button>
                <a href="{{ route('report_pasteurs.index', $report->uuid) }}" class="btn btn-secondary mt-3">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let detailIndex = -1;

    function initSignaturePad(detailElem) {
        detailElem.querySelectorAll("canvas").forEach((canvas) => {
            // Buat SignaturePad per canvas
            const pad = new SignaturePad(canvas, {
                backgroundColor: "rgba(255,255,255,0)",
                penColor: "rgb(0,0,0)"
            });
            // Simpan pad di canvas sendiri
            canvas.signaturePad = pad;

            // Ambil input hidden berikutnya
            const hiddenInput = canvas.nextElementSibling;
            const clearBtn = canvas.parentElement.querySelector(".clear-signature");
            if (clearBtn) {
                clearBtn.addEventListener("click", () => {
                    pad.clear();
                    hiddenInput.value = "";
                });
            }
        });
    }

    function addDetail() {
        detailIndex++;
        const template = document.getElementById("detail-template");
        const clone = template.cloneNode(true);
        clone.id = "";
        clone.classList.remove("d-none");

        // Update product & detail inputs
        clone.querySelectorAll("select, input, textarea").forEach(input => {
            if (input.classList.contains('product-select')) input.name =
                `details[${detailIndex}][product_uuid]`;
            if (input.classList.contains('program-number')) input.name =
                `details[${detailIndex}][program_number]`;
            if (input.classList.contains('product-code')) input.name =
                `details[${detailIndex}][product_code]`;
            if (input.classList.contains('for-packaging-gr')) input.name =
                `details[${detailIndex}][for_packaging_gr]`;
            if (input.classList.contains('trolley-count')) input.name =
                `details[${detailIndex}][trolley_count]`;
            if (input.classList.contains('product-temp')) input.name =
                `details[${detailIndex}][product_temp]`;
            if (input.classList.contains('qc-input')) input.name = `details[${detailIndex}][qc_paraf]`;
            if (input.classList.contains('prod-input')) input.name =
                `details[${detailIndex}][production_paraf]`;
            if (input.classList.contains('product-core-temp')) input.name =
                `details[${detailIndex}][steps][8][data][product_core_temp]`;
            if (input.classList.contains('sortation')) input.name =
                `details[${detailIndex}][steps][8][data][sortation]`;
        });

        // Update steps hidden fields
        clone.querySelectorAll(".step-item").forEach((stepElem, stepIndex) => {
            stepElem.querySelector(".step-name").name =
                `details[${detailIndex}][steps][${stepIndex}][step_name]`;
            stepElem.querySelector(".step-order").name =
                `details[${detailIndex}][steps][${stepIndex}][step_order]`;
            stepElem.querySelector(".step-type").name =
                `details[${detailIndex}][steps][${stepIndex}][step_type]`;

            // Update step data inputs
            stepElem.querySelectorAll("input").forEach(input => {
                if (input.classList.contains('start-time')) input.name =
                    `details[${detailIndex}][steps][${stepIndex}][data][start_time]`;
                if (input.classList.contains('end-time')) input.name =
                    `details[${detailIndex}][steps][${stepIndex}][data][end_time]`;
                if (input.classList.contains('water-temp')) input.name =
                    `details[${detailIndex}][steps][${stepIndex}][data][water_temp]`;
                if (input.classList.contains('pressure')) input.name =
                    `details[${detailIndex}][steps][${stepIndex}][data][pressure]`;
            });
        });

        // Hapus detail
        clone.querySelector(".remove-detail").addEventListener("click", () => {
            clone.remove();
        });

        document.getElementById("detail-wrapper").appendChild(clone);
        initSignaturePad(clone);
    }

    document.getElementById("add-detail").addEventListener("click", addDetail);

    // Tambahkan satu detail awal
    addDetail();

    // Submit → save signature
    document.querySelector("form#pasteur-form").addEventListener("submit", function() {
        document.querySelectorAll("canvas").forEach(canvas => {
            if (canvas.signaturePad && !canvas.signaturePad.isEmpty()) {
                const hiddenInput = canvas.nextElementSibling;
                if (hiddenInput) hiddenInput.value = canvas.signaturePad.toDataURL("image/png");
            }
        });
    });
});
</script>
@endsection