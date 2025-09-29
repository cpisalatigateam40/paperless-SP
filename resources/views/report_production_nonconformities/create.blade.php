@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Laporan Verifikasi Ketidaksesuaian Proses Produksi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_production_nonconformities.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="d-flex">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control">
                    </div>
                </div>

                <h4 class="mt-5 mb-4">Detail Ketidaksesuaian</h4>

                <div id="detail-container">
                    <div class="detail-item mb-4 border p-3 rounded">
                        <div class="mb-3">
                            <label>Jam</label>
                            <input type="time" name="details[0][occurrence_time]" class="form-control"
                                value="{{ now()->format('H:i') }}">
                        </div>
                        <div class="mb-3">
                            <label>Ketidaksesuaian</label>
                            <textarea name="details[0][description]" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Jumlah</label>
                                <input type="number" name="details[0][quantity]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Satuan</label>
                                <select name="details[0][unit]" class="form-control">
                                    <option value="">-- Pilih satuan --</option>
                                    <option value="Kemasan">Kemasan</option>
                                    <option value="Pack">Pack</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Kategori Bahaya</label>
                            <select name="details[0][hazard_category]" class="form-control">
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
                            <select name="details[0][disposition]" class="form-control">
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
                            <input type="file" name="details[0][evidence]" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea name="details[0][remark]" class="form-control"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button>
                    </div>
                </div>

                <button type="button" id="add-detail" class="btn btn-outline-secondary">+ Tambah Detail</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailIndex = 1;

    document.getElementById('add-detail').addEventListener('click', function() {
        const container = document.getElementById('detail-container');
        const template = container.querySelector('.detail-item').cloneNode(true);

        // Reset input value
        template.querySelectorAll('input, textarea, select').forEach(function(input) {
            input.value = '';
        });

        // Update name attributes sesuai index baru
        template.querySelectorAll('input, textarea, select').forEach(function(input) {
            input.name = input.name.replace(/\[\d+\]/, `[${detailIndex}]`);
        });

        container.appendChild(template);
        detailIndex++;
    });

    // Hapus detail
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-detail')) {
            const item = e.target.closest('.detail-item');
            if (document.querySelectorAll('.detail-item').length > 1) {
                item.remove();
            } else {
                alert('Harus ada minimal satu detail.');
            }
        }
    });
});
</script>
@endsection