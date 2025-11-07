@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-foreign-objects.update', $report->uuid) }}" id="foreign-object-form" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- HEADER FORM --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Edit Laporan Verifikasi Kontaminasi Benda Asing</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" id="date" name="date" class="form-control"  value="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="shift" class="form-label">Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section</label>
                        <select id="section" name="section_uuid" class="form-control" required>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}" {{ $section->uuid == $report->section_uuid ? 'selected' : '' }}>{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h5 class="mt-4 mb-2">Detail Temuan Kontaminasi</h5>
                <div id="detail-container">
                    @foreach($report->details as $i => $detail)
                        <div class="row mb-3 border p-3 rounded">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam</label>
                                <input type="time" name="details[{{ $i }}][time]" class="form-control" value="{{ $detail->time }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Produk</label>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control select2-product" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}" {{ $product->uuid == $detail->product_uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }} - {{ $product->nett_weight }} g
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control" value="{{ $detail->production_code }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kontaminan</label>
                                <input type="text" name="details[{{ $i }}][contaminant_type]" class="form-control" value="{{ $detail->contaminant_type }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bukti (Foto)</label>
                                <input type="file" name="details[{{ $i }}][evidence]" class="form-control">
                                @if($detail->evidence)
                                    <small>File saat ini: <a href="{{ asset('storage/'.$detail->evidence) }}" target="_blank">Lihat</a></small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahapan Analisis</label>
                                <input type="text" name="details[{{ $i }}][analysis_stage]" class="form-control" value="{{ $detail->analysis_stage }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Asal Kontaminan</label>
                                <input type="text" name="details[{{ $i }}][contaminant_origin]" class="form-control" value="{{ $detail->contaminant_origin }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="details[{{ $i }}][notes]" class="form-control" value="{{ $detail->notes }}">
                            </div>

                            {{-- Tanda tangan --}}
                            <div class="col-md-4">
                                <label class="form-label">Paraf QC</label>
                                <canvas id="qc-canvas-{{ $i }}" class="border" width="300" height="150" data-input="qc_paraf_input_{{ $i }}"></canvas>
                                <input type="hidden" name="details[{{ $i }}][qc_paraf]" id="qc_paraf_input_{{ $i }}" value="{{ $detail->qc_paraf }}">
                                @if($detail->qc_paraf)
                                    <small><a href="{{ asset('storage/'.$detail->qc_paraf) }}" target="_blank">Lihat</a></small>
                                @endif
                                <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paraf Produksi</label>
                                <canvas id="prod-canvas-{{ $i }}" class="border" width="300" height="150" data-input="production_paraf_input_{{ $i }}"></canvas>
                                <input type="hidden" name="details[{{ $i }}][production_paraf]" id="production_paraf_input_{{ $i }}" value="{{ $detail->production_paraf }}">
                                @if($detail->production_paraf)
                                    <small><a href="{{ asset('storage/'.$detail->production_paraf) }}" target="_blank">Lihat</a></small>
                                @endif
                                <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paraf Engineering</label>
                                <canvas id="eng-canvas-{{ $i }}" class="border" width="300" height="150" data-input="engineering_paraf_input_{{ $i }}"></canvas>
                                <input type="hidden" name="details[{{ $i }}][engineering_paraf]" id="engineering_paraf_input_{{ $i }}" value="{{ $detail->engineering_paraf }}">
                                @if($detail->engineering_paraf)
                                    <small><a href="{{ asset('storage/'.$detail->engineering_paraf) }}" target="_blank">Lihat</a></small>
                                @endif
                                <button type="button" class="btn btn-sm btn-secondary mt-1 clear-signature">Hapus</button>
                            </div>

                        </div>
                    @endforeach
                </div>

                <button class="btn btn-success mt-3">Update Laporan</button>
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

    document.querySelectorAll("canvas").forEach((canvas) => {
        const pad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: "rgb(0,0,0)"
        });

        // Jika ada base64 value, load ke canvas
        const inputId = canvas.dataset.input;
        const input = document.getElementById(inputId);
        if(input && input.value){
            const img = new Image();
            img.src = input.value;
            img.onload = function(){
                pad.fromDataURL(input.value);
            }
        }

        signaturePads.push({pad, canvas});

        const clearBtn = canvas.parentElement.querySelector(".clear-signature");
        if(clearBtn){
            clearBtn.addEventListener("click", function(){
                pad.clear();
                if(inputId) document.getElementById(inputId).value = '';
            });
        }
    });

    document.querySelector("form#foreign-object-form").addEventListener("submit", function(){
        signaturePads.forEach(({pad, canvas})=>{
            const inputId = canvas.dataset.input;
            if(!inputId) return;
            const input = document.getElementById(inputId);
            if(!input) return;
            if(!pad.isEmpty()){
                input.value = pad.toDataURL("image/png");
            }
        });
    });
});
</script>
@endsection
