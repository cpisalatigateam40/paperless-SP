@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-foreign-objects.store-detail', $report->uuid) }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="card shadow mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>Tambah Detail Temuan Kontaminasi</h5>

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
                        <label class="form-label">Keterangan (ntuk DisposisiU)</label>
                        <input type="text" name="notes" class="form-control">
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