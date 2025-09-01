@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail Ketidaksesuaian untuk Tanggal {{ $report->date }} - Shift {{ $report->shift }}</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_production_nonconformities.store-detail', $report->uuid) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label>Jam</label>
                    <input type="time" name="occurrence_time" class="form-control" value="{{ now()->format('H:i') }}">
                </div>
                <div class="mb-3">
                    <label>Deskripsi Ketidaksesuaian</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Jumlah</label>
                    <input type="number" name="quantity" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Kategori Bahaya</label>
                    <select name="hazard_category" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Fisik">Fisik</option>
                        <option value="Kimia">Kimia</option>
                        <option value="Allergen">Allergen</option>
                        <option value="Radiologi">Radiologi</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Disposisi</label>
                    <select name="disposition" class="form-control">
                        <option value="">-- Pilih Disposisi --</option>
                        <option value="Rework">Rework</option>
                        <option value="Repack">Repack</option>
                        <option value="Sortir">Sortir</option>
                        <option value="Return">Return</option>
                        <option value="Dimusnahkan">Dimusnahkan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bukti (Foto)</label>
                    <input type="file" name="evidence" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="remark" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>
@endsection