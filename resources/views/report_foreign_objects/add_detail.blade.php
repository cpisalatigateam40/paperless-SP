@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-foreign-objects.store-detail', $report->uuid) }}" method="POST"
        enctype="multipart/form-data" id="foreign-object-form">
        @csrf

        <div class="card shadow mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>Tambah Detail Laporan Verifikasi Kontaminasi Benda Asing</h5>

            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Jam</label>
                        <input type="time" name="time" class="form-control"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Produk</label>
                        <select name="product_uuid" class="form-control" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kontaminan</label>
                        <input type="text" name="contaminant_type" class="form-control">
                    </div>
                </div>


                <div class="row mt-4 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Bukti (Foto)</label>
                        <input type="file" name="evidence" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahapan Analisis</label>
                        <input type="text" name="analysis_stage" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Asal Kontaminan</label>
                        <input type="text" name="contaminant_origin" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Paraf QC</label>
                        <canvas id="qc-canvas" class="border" width="300" height="150"
                            data-input="qc_paraf_input"></canvas>
                        <input type="hidden" name="qc_paraf" id="qc_paraf_input">
                        <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Paraf Produksi</label>
                        <canvas id="prod-canvas" class="border" width="300" height="150"
                            data-input="production_paraf_input"></canvas>
                        <input type="hidden" name="production_paraf" id="production_paraf_input">
                        <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Paraf Engineering</label>
                        <canvas id="eng-canvas" class="border" width="300" height="150"
                            data-input="engineering_paraf_input"></canvas>
                        <input type="hidden" name="engineering_paraf" id="engineering_paraf_input">
                        <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                    </div>
                </div>



                <div class="d-flex mt-4" style="gap: .5rem;">
                    <button class="btn btn-success">Simpan Detail</button>
                    <a href="{{ route('report-foreign-objects.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
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