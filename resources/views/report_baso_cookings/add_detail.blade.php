@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_baso_cookings.store_detail', $report->uuid) }}" id="baso-detail-form" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tambah Detail Laporan Verifikasi Pemasakan Baso</h5>
                <small class="text-muted">Report: {{ $report->date }} | Shift: {{ $report->shift }} | Produk:
                    {{ $report->product->product_name ?? '-' }}</small>
            </div>

            <div class="card-body">
                {{-- DETAIL REPORT --}}
                <div id="detail-wrapper">
                    <div class="detail-item border rounded p-3 mb-3">
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label>Kode Produksi Kemasan</label>
                                <input type="text" name="details[0][production_code]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Suhu Emulsi</label>
                                <input type="number" step="0.01" name="details[0][emulsion_temp]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Suhu Air Tangki 1</label>
                                <input type="number" step="0.01" name="details[0][boiling_tank_temp_1]"
                                    class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Suhu Air Tangki 2</label>
                                <input type="number" step="0.01" name="details[0][boiling_tank_temp_2]"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label>Berat Awal</label>
                                <input type="number" step="0.01" name="details[0][initial_weight]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Berat Akhir</label>
                                <input type="number" step="0.01" name="details[0][final_weight]" class="form-control">
                            </div>
                        </div>

                        {{-- Temperatures --}}
                        <h6 class="mt-3">Suhu Baso</h6>
                        <div class="temperatures-wrapper">
                            <div class="temperature-item row mb-2">
                                <!-- <div class="col-md-2">
                                    <select name="details[0][temperatures][0][time_type]" class="form-control">
                                        <option value="awal">Awal</option>
                                        <option value="akhir">Akhir</option>
                                    </select>
                                </div> -->
                                <div class="col-md-3">
                                    <input type="time" name="details[0][temperatures][0][time_recorded]"
                                        class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                @for($i = 1; $i <= 5; $i++) <div class="col-md-1">
                                    <input type="number" step="0.01"
                                        name="details[0][temperatures][0][baso_temp_{{ $i }}]"
                                        class="form-control baso-temp-input" data-group="0-0" placeholder="&deg;C">
                            </div>
                            @endfor
                            <div class="col-md-3">
                                <input type="number" step="0.01" name="details[0][temperatures][0][avg_baso_temp]"
                                    class="form-control avg-baso-temp" data-group="0-0" placeholder="Rata-rata"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Sensory Checks --}}
                    <h6 class="mt-4 mb-3">Pemeriksaan Sensory</h6>
                    <div class="row mb-2">
                        @foreach(['shape' => 'Bentuk', 'taste' => 'Rasa', 'aroma' => 'Aroma', 'texture' => 'Tekstur',
                        'color' => 'Warna'] as $key => $label)
                        <div class="col-md-2">
                            <label>{{ $label }}</label>
                            <select name="details[0][sensory_{{ $key }}]" class="form-control">
                                <option value="1">OK</option>
                                <option value="0">Tidak OK</option>
                            </select>
                        </div>
                        @endforeach
                    </div>

                    {{-- Parafer --}}
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Paraf QC</label>
                            <canvas id="qc-canvas-0" class="border" width="300" height="150"
                                data-input="qc_paraf_input_0"></canvas>
                            <input type="hidden" name="details[0][qc_paraf]" id="qc_paraf_input_0">
                            <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Paraf Produksi</label>
                            <canvas id="prod-canvas-0" class="border" width="300" height="150"
                                data-input="prod_paraf_input_0"></canvas>
                            <input type="hidden" name="details[0][prod_paraf]" id="prod_paraf_input_0">
                            <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('report_baso_cookings.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Simpan Detail</button>
        </div>
</div>
</form>
</div>

{{-- Script untuk signature dan rata-rata suhu --}}
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const signaturePads = [];

    document.querySelectorAll("canvas").forEach((canvas) => {
        const pad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: "rgb(0,0,0)"
        });
        signaturePads.push({
            pad,
            canvas
        });

        const clearBtn = canvas.parentElement.querySelector(".clear-signature");
        if (clearBtn) {
            clearBtn.addEventListener("click", function() {
                pad.clear();
                const inputId = canvas.dataset.input;
                if (inputId) document.getElementById(inputId).value = "";
            });
        }
    });

    document.querySelector("form#baso-detail-form").addEventListener("submit", function() {
        signaturePads.forEach(({
            pad,
            canvas
        }) => {
            const inputId = canvas.dataset.input;
            if (!inputId) return;
            const input = document.getElementById(inputId);
            if (!input) return;
            if (!pad.isEmpty()) {
                input.value = pad.toDataURL("image/png");
            }
        });
    });

    // Hitung rata-rata suhu realtime
    document.addEventListener("input", function(e) {
        if (e.target.classList.contains("baso-temp-input")) {
            const group = e.target.dataset.group;
            const inputs = document.querySelectorAll(`.baso-temp-input[data-group="${group}"]`);
            let sum = 0,
                count = 0;
            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    sum += val;
                    count++;
                }
            });
            const avg = count > 0 ? (sum / count).toFixed(2) : "";
            const avgField = document.querySelector(`.avg-baso-temp[data-group="${group}"]`);
            if (avgField) avgField.value = avg;
        }
    });
});
</script>
@endsection