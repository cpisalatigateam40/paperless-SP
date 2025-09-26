@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Detail Laporan Verifikasi Pemasakan Dengan Steamer</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('report_rtg_steamers.store_detail', $report->uuid) }}" method="POST"
                id="steamers-form" enctype="multipart/form-data">
                @csrf



                <div id="detailWrapper">
                    <div class="detail-block border rounded p-3 mb-3">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Steamer</label>
                                <input type="text" name="details[0][steamer]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Jumlah Trolly</label>
                                <input type="number" name="details[0][trolley_count]" class="form-control">
                            </div>
                        </div>

                        <h5 class="mt-4">Streaming</h5>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Suhu Ruang (°C)</label>
                                <input type="number" step="0.01" name="details[0][room_temp]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Suhu Produk (°C)</label>
                                <input type="number" step="0.01" name="details[0][product_temp]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Waktu (menit)</label>
                                <input type="number" name="details[0][time_minute]" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>Jam Mulai</label>
                                <input type="time" name="details[0][start_time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label>Jam Selesai</label>
                                <input type="time" name="details[0][end_time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                            </div>
                        </div>

                        <h5 class="mt-4">Sensori</h5>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Kematangan</label>
                                <select name="details[0][sensory_ripeness]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Rasa</label>
                                <select name="details[0][sensory_taste]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Aroma</label>
                                <select name="details[0][sensory_aroma]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>Tekstur</label>
                                <select name="details[0][sensory_texture]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Warna</label>
                                <select name="details[0][sensory_color]" class="form-control">
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                        </div>

                        <!-- <h6 class="mt-3">Paraf</h6>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Paraf QC</label>
                                <canvas id="qc-canvas-0" class="border" width="300" height="150"
                                    data-input="qc_paraf_input_0"></canvas>
                                <input type="hidden" name="details[0][qc_paraf]" id="qc_paraf_input_0">
                                <button type="button"
                                    class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paraf Produksi</label>
                                <canvas id="prod-canvas-0" class="border" width="300" height="150"
                                    data-input="production_paraf_input_0"></canvas>
                                <input type="hidden" name="details[0][production_paraf]" id="production_paraf_input_0">
                                <button type="button"
                                    class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>
                        </div> -->

                        <!-- <div class="text-end">
                            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDetail(this)">Hapus
                                Detail</button>
                        </div> -->
                    </div>
                </div>

                <!-- <button type="button" class="btn btn-success btn-sm mb-3" onclick="addDetail()">+ Tambah Detail</button> -->

                <div>
                    <button type="submit" class="btn btn-primary">Simpan Detail</button>
                    <a href="{{ route('report_rtg_steamers.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let detailIndex = 1;

function addDetail() {
    let wrapper = document.getElementById('detailWrapper');
    let html = document.querySelector('.detail-block').outerHTML;
    html = html.replaceAll('[0]', `[${detailIndex}]`);
    html = html.replaceAll('-0', `-${detailIndex}`);
    html = html.replaceAll('_0', `_${detailIndex}`);
    wrapper.insertAdjacentHTML('beforeend', html);
    detailIndex++;
}

function removeDetail(button) {
    if (document.querySelectorAll('.detail-block').length > 1) {
        button.closest('.detail-block').remove();
    } else {
        alert('Minimal 1 detail harus ada');
    }
}
</script>
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
    document.querySelector("form#steamers-form").addEventListener("submit", function() {
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