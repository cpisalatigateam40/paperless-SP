@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_baso_cookings.update_next', $report->uuid) }}" id="baso-cooking-form" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">Edit Laporan Verifikasi Pemasakan Baso</h5>
            </div>

            <div class="card-body">
                {{-- HEADER REPORT --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" id="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="shift" class="form-label">Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" value="{{ $report->shift }}"
                            required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Jenis Produk</label>
                        <select name="product_uuid" class="form-control select2-product">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->uuid }}" {{ $p->uuid == $report->product_uuid ? 'selected' : '' }}>
                                {{ $p->product_name }} - {{ $p->nett_weight }} g
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Std Suhu Pusat</label>
                        <input type="text" name="std_core_temp" class="form-control"
                            value="{{ $report->std_core_temp }}">
                    </div>
                    <div class="col-md-6">
                        <label>Std Berat Akhir/Potong</label>
                        <input type="text" name="std_weight" class="form-control" value="{{ $report->std_weight }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Set Suhu Tangki Perebusan 1</label>
                        <input type="number" step="0.01" name="set_boiling_1" class="form-control"
                            value="{{ $report->set_boiling_1 }}">
                    </div>
                    <div class="col-md-6">
                        <label>Set Suhu Tangki Perebusan 2</label>
                        <input type="number" step="0.01" name="set_boiling_2" class="form-control"
                            value="{{ $report->set_boiling_2 }}">
                    </div>
                </div>

                {{-- DETAIL REPORT --}}
                <hr>
                <h6>Detail Produksi</h6>
                <div id="detail-wrapper">
                    @foreach($details as $i => $detail)
                    <div class="detail-item border rounded p-3 mb-3">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Kode Produksi Kemasan</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                    value="{{ $detail->production_code }}">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Emulsi</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][emulsion_temp]"
                                    class="form-control" value="{{ $detail->emulsion_temp }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Suhu Air Tangki 1</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][boiling_tank_temp_1]"
                                    class="form-control" value="{{ $detail->boiling_tank_temp_1 }}">
                            </div>
                            <div class="col-md-6">
                                <label>Suhu Air Tangki 2</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][boiling_tank_temp_2]"
                                    class="form-control" value="{{ $detail->boiling_tank_temp_2 }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Berat Awal</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][initial_weight]"
                                    class="form-control" value="{{ $detail->initial_weight }}">
                            </div>
                            <div class="col-md-6">
                                <label>Berat Akhir</label>
                                <input type="number" step="0.01" name="details[{{ $i }}][final_weight]"
                                    class="form-control" value="{{ $detail->final_weight }}">
                            </div>
                        </div>

                        {{-- Suhu Baso --}}
                        <h6 class="mt-3">Suhu Baso</h6>
                        <div class="temperatures-wrapper">
                            @foreach($detail->temperatures as $j => $temp)
                            <div class="temperature-item row mb-2">
                                <div class="col-md-3">
                                    <input type="time" name="details[{{ $i }}][temperatures][{{ $j }}][time_recorded]"
                                        class="form-control" value="{{ $temp->time_recorded }}">
                                </div>
                                @for($k=1; $k<=5; $k++) <div class="col-md-1">
                                    <input type="number" step="0.01"
                                        name="details[{{ $i }}][temperatures][{{ $j }}][baso_temp_{{ $k }}]"
                                        class="form-control baso-temp-input" data-group="{{ $i }}-{{ $j }}"
                                        value="{{ $temp->{'baso_temp_'.$k} }}">
                            </div>
                            @endfor
                            <div class="col-md-3">
                                <input type="number" step="0.01"
                                    name="details[{{ $i }}][temperatures][{{ $j }}][avg_baso_temp]"
                                    class="form-control avg-baso-temp" data-group="{{ $i }}-{{ $j }}"
                                    value="{{ $temp->avg_baso_temp }}" readonly>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Sensory --}}
                    <h6 class="mt-5 mb-3">Pemeriksaan Sensory</h6>
                    <div class="row mb-2">
                        @foreach(['shape'=>'Bentuk','taste'=>'Rasa','aroma'=>'Aroma','texture'=>'Tekstur','color'=>'Warna']
                        as $key => $label)
                        <div class="col-md-2">
                            <label>{{ $label }}</label>
                            <select name="details[{{ $i }}][sensory_{{ $key }}]" class="form-control">
                                <option value="1" {{ $detail->{'sensory_'.$key} == 1 ? 'selected' : '' }}>OK</option>
                                <option value="0" {{ $detail->{'sensory_'.$key} == 0 ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>
                        @endforeach
                    </div>

                    {{-- Paraf --}}
                    <div class="row mb-2 mt-5">
                        <div class="col-md-6">
                            <label class="form-label">Paraf QC</label>
                            @if($detail->qc_paraf)
                            <img src="{{ asset('storage/'.$detail->qc_paraf) }}" class="border mb-2" width="300"
                                height="150">
                            @endif
                            <canvas id="qc-canvas-{{ $i }}" class="border" width="300" height="150"
                                data-input="qc_paraf_input_{{ $i }}"></canvas>
                            <input type="hidden" name="details[{{ $i }}][qc_paraf]" id="qc_paraf_input_{{ $i }}">
                            <button type="button" class="btn btn-sm btn-secondary clear-signature">Hapus</button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paraf Produksi</label>
                            @if($detail->prod_paraf)
                            <img src="{{ asset('storage/'.$detail->prod_paraf) }}" class="border mb-2" width="300"
                                height="150">
                            @endif
                            <canvas id="prod-canvas-{{ $i }}" class="border" width="300" height="150"
                                data-input="prod_paraf_input_{{ $i }}"></canvas>
                            <input type="hidden" name="details[{{ $i }}][prod_paraf]" id="prod_paraf_input_{{ $i }}">
                            <button type="button" class="btn btn-sm btn-secondary clear-signature">Hapus</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('report_baso_cookings.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
</div>
</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const signaturePads = [];

    // 1️⃣ Inisialisasi SignaturePad untuk semua canvas
    document.querySelectorAll("canvas").forEach((canvas) => {
        const pad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: "rgb(0,0,0)"
        });

        signaturePads.push({
            pad,
            canvas
        });

        // tombol hapus tanda tangan
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

    // 2️⃣ Konversi tanda tangan jadi base64 saat submit
    document.querySelector("form#baso-cooking-form").addEventListener("submit", function() {
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
            }
        });
    });

    // 3️⃣ Fungsi untuk menghitung rata-rata suhu baso
    function updateAverage(group) {
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

    // 4️⃣ Event listener untuk input suhu
    document.addEventListener("input", function(e) {
        if (e.target.classList.contains("baso-temp-input")) {
            const group = e.target.dataset.group;
            updateAverage(group);
        }
    });

    // 5️⃣ Hitung ulang semua avg suhu saat page load (data edit)
    document.querySelectorAll(".avg-baso-temp").forEach((avgField) => {
        const group = avgField.dataset.group;
        updateAverage(group);
    });
});
</script>
@endsection