@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-foreign-objects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- HEADER FORM --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Form Pemeriksaan Kontaminasi</h5>
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
                                <div class="col-md-4">
                                    <label class="form-label">Bukti (Foto)</label>
                                    <input type="file" name="details[0][evidence]" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tahapan Analisis</label>
                                    <input type="text" name="details[0][analysis_stage]" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Asal Kontaminan</label>
                                    <input type="text" name="details[0][contaminant_origin]" class="form-control">
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