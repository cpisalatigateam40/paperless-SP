@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-foreign-objects.store') }}" id="foreign-object-form" method="POST"
        enctype="multipart/form-data">
        @csrf

        {{-- HEADER FORM --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Tambah Laporan Verifikasi Kontaminasi Bneda Asing</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" id="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="shift" class="form-label">Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section</label>
                        <select id="section" name="section_uuid" class="form-control" required>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top: 3rem;">
                    <h5 style="margin-bottom: 2rem;">Detail Temuan Kontaminasi</h5>
                    <div>
                        <div id="detail-container">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Jam</label>
                                    <input type="time" name="details[0][time]" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Produk</label>
                                    <select name="details[0][product_uuid]" class="form-control" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Kode Produksi</label>
                                    <input type="text" name="details[0][production_code]" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jenis Kontaminan</label>
                                    <input type="text" name="details[0][contaminant_type]" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Bukti (Foto)</label>
                                    <input type="file" name="details[0][evidence]" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tahapan Analisis</label>
                                    <input type="text" name="details[0][analysis_stage]" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Asal Kontaminan</label>
                                    <input type="text" name="details[0][contaminant_origin]" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="details[0][notes]" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
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
                                    <input type="hidden" name="details[0][production_paraf]"
                                        id="production_paraf_input_0">
                                    <button type="button"
                                        class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Paraf Engineering</label>
                                    <canvas id="eng-canvas-0" class="border" width="300" height="150"
                                        data-input="engineering_paraf_input_0"></canvas>
                                    <input type="hidden" name="details[0][engineering_paraf]"
                                        id="engineering_paraf_input_0">
                                    <button type="button"
                                        class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <button class="btn btn-success mt-3">Simpan Laporan</button>
            </div>
        </div>
    </form>
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
    document.querySelector("form#foreign-object-form").addEventListener("submit", function() {
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