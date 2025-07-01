@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Tambah Detail Pemeriksaan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_magnet_traps.details.store', $report->uuid) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Tanggal</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control"
                            value="{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}" readonly>
                    </div>
                    <label class="col-md-2 col-form-label">Shift</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="{{ $report->shift }}" readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Section</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" value="{{ $report->section->section_name ?? '-' }}"
                            readonly>
                    </div>
                </div>

                <hr>
                <h5>Input Detail Pemeriksaan</h5>

                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Jam</label>
                    <div class="col-md-4">
                        <input type="time" name="time" class="form-control" required>
                    </div>
                    <label class="col-md-2 col-form-label">Sumber</label>
                    <div class="col-md-4 d-flex align-items-center" style="gap: 2rem;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="source" value="QC" required>
                            <label class="form-check-label">QC</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="source" value="Produksi">
                            <label class="form-check-label">Produksi</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Temuan (Upload Gambar)</label>
                    <div class="col-md-10">
                        <input type="file" name="finding" class="form-control" accept="image/*" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Keterangan</label>
                    <div class="col-md-10">
                        <input type="text" name="note" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end" style="gap: .5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Detail</button>
                    <a href="{{ route('report_magnet_traps.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection