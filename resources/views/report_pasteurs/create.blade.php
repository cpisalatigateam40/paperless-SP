@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Tambah Laporan Verifikasi Pasteurisasi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_pasteurs.store') }}" method="POST" id="pasteur-form"
                enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" value="{{ \Carbon\Carbon::today()->toDateString() }}"
                            class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}">
                    </div>

                </div>

                <h5 class="mt-4">Detail Produk</h5>
                <div id="detail-wrapper">
                    <div class="border p-3 mb-3 detail-item">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Produk</label>
                                <select name="details[0][product_uuid]" class="form-control select2-product">
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }} -
                                        {{ $product->nett_weight }} g</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Nomor Program</label>
                                <input type="text" name="details[0][program_number]" class="form-control">
                            </div>

                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Kode Produk</label>
                                <input type="text" name="details[0][product_code]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Kemasan (gr)</label>
                                <input type="number" name="details[0][for_packaging_gr]" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Jumlah Troly</label>
                                <input type="number" name="details[0][trolley_count]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.1" name="details[0][product_temp]" class="form-control">
                            </div>
                        </div>

                        {{-- ===================== STEPS ===================== --}}
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

                        @foreach($standardSteps as $order => $name)
                        <div class="p-2 border mb-4">
                            <strong>{{ $order }}. {{ $name }}</strong>
                            <div class="row mb-4 mt-2">
                                <input type="hidden" name="details[0][steps][{{ $loop->index }}][step_name]"
                                    value="{{ $name }}">
                                <input type="hidden" name="details[0][steps][{{ $loop->index }}][step_order]"
                                    value="{{ $order }}">
                                <input type="hidden" name="details[0][steps][{{ $loop->index }}][step_type]"
                                    value="standard">

                                <div class="col-md-3">
                                    <label>Jam Mulai</label>
                                    <input type="time" name="details[0][steps][{{ $loop->index }}][data][start_time]"
                                        class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Jam Selesai</label>
                                    <input type="time" name="details[0][steps][{{ $loop->index }}][data][end_time]"
                                        class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Suhu Air (°C)</label>
                                    <input type="number" step="0.1"
                                        name="details[0][steps][{{ $loop->index }}][data][water_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Tekanan (Bar)</label>
                                    <input type="number" step="0.01"
                                        name="details[0][steps][{{ $loop->index }}][data][pressure]"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{-- Step 8: Drainage --}}
                        <div class="p-2 border  mb-4">
                            <strong>Step 8: Drainage Pressure</strong>
                            <div class="row mb-4 mt-2">
                                <input type="hidden" name="details[0][steps][7][step_name]" value="Drainage">
                                <input type="hidden" name="details[0][steps][7][step_order]" value="8">
                                <input type="hidden" name="details[0][steps][7][step_type]" value="drainage">

                                <div class="col-md-6">
                                    <label>Jam Mulai</label>
                                    <input type="time" name="details[0][steps][7][data][start_time]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>Jam Selesai</label>
                                    <input type="time" name="details[0][steps][7][data][end_time]" class="form-control">
                                </div>
                            </div>
                        </div>

                        {{-- Step 9: Finish --}}
                        <div class="p-2 border  mb-4">
                            <strong>Step 9: Finish Produk</strong>
                            <div class="row mb-4 mt-2">
                                <input type="hidden" name="details[0][steps][8][step_name]" value="Finish">
                                <input type="hidden" name="details[0][steps][8][step_order]" value="9">
                                <input type="hidden" name="details[0][steps][8][step_type]" value="finish">

                                <div class="col-md-6">
                                    <label>Suhu Inti Produk (°C)</label>
                                    <input type="number" step="0.1" name="details[0][steps][8][data][product_core_temp]"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>Sortasi</label>
                                    <input type="text" name="details[0][steps][8][data][sortation]"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                        {{-- ================================================= --}}

                    </div>
                </div>

                <h6 class="mt-3">Paraf</h6>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Paraf QC</label>
                        <canvas id="qc-canvas-0" class="border" width="300" height="150"
                            data-input="qc_paraf_input_0"></canvas>
                        <input type="hidden" name="details[0][qc_paraf]" id="qc_paraf_input_0">
                        <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Paraf Produksi</label>
                        <canvas id="prod-canvas-0" class="border" width="300" height="150"
                            data-input="production_paraf_input_0"></canvas>
                        <input type="hidden" name="details[0][production_paraf]" id="production_paraf_input_0">
                        <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label>Problem</label>
                        <textarea name="problem" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Corrective Action</label>
                        <textarea name="corrective_action" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-4">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const signaturePads = [];

    // Init SignaturePad untuk semua canvas
    document.querySelectorAll("canvas").forEach((canvas) => {
        const pad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: "rgb(0,0,0)"
        });

        signaturePads.push({
            pad,
            canvas
        });

        // Tombol clear
        const clearBtn = canvas.parentElement.querySelector(".clear-signature");
        if (clearBtn) {
            clearBtn.addEventListener("click", function() {
                pad.clear();
                const inputId = canvas.dataset.input;
                if (inputId) {
                    document.getElementById(inputId).value = "";
                }
            });
        }
    });

    // Saat submit form → convert ke base64
    document.querySelector("form#pasteur-form").addEventListener("submit", function() {
        signaturePads.forEach(({
            pad,
            canvas
        }) => {
            const inputId = canvas.dataset.input;
            if (!inputId) return;

            const input = document.getElementById(inputId);
            if (!input) return;

            if (!pad.isEmpty()) {
                const dataUrl = pad.toDataURL("image/png");
                input.value = dataUrl;
                console.log("SET SIGNATURE", inputId, dataUrl.substring(0, 50));
            } else {
                console.log("EMPTY SIGNATURE", inputId);
            }

        });
    });
});
</script>
@endsection