@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Ketidaksesuaian Proses Produksi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_production_nonconformities.update', $report->uuid) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="d-flex">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}">
                    </div>
                </div>

                <h4 class="mt-5 mb-4">Detail Ketidaksesuaian</h4>

                <div id="detail-container">
                    @foreach($report->details as $i => $detail)
                        <div class="detail-item mb-4 border p-3 rounded">
                            <div class="mb-3">
                                <label>Jam</label>
                                <input type="time" name="details[{{ $i }}][occurrence_time]" class="form-control"
                                    value="{{ $detail->occurrence_time }}">
                            </div>
                            <div class="mb-3">
                                <label>Ketidaksesuaian</label>
                                <textarea name="details[{{ $i }}][description]" class="form-control" rows="3">{{ $detail->description }}</textarea>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Jumlah</label>
                                    <input type="number" name="details[{{ $i }}][quantity]" class="form-control"
                                        value="{{ $detail->quantity }}">
                                </div>
                                <div class="col-md-6">
                                    <label>Satuan</label>
                                    <select name="details[{{ $i }}][unit]" class="form-control">
                                        <option value="">-- Pilih satuan --</option>
                                        <option value="Kemasan" {{ $detail->unit=='Kemasan' ? 'selected' : '' }}>Kemasan</option>
                                        <option value="Pack" {{ $detail->unit=='Pack' ? 'selected' : '' }}>Pack</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Kategori Bahaya</label>
                                <select name="details[{{ $i }}][hazard_category]" class="form-control">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach(['Biologi','Fisik','Kimia','Allergen','Radiologi'] as $cat)
                                        <option value="{{ $cat }}" {{ $detail->hazard_category==$cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Disposisi</label>
                                <select name="details[{{ $i }}][disposition]" class="form-control">
                                    <option value="">-- Pilih Disposisi --</option>
                                    @foreach(['Rework','Repack','Sortir','Return','Dimusnahkan'] as $disp)
                                        <option value="{{ $disp }}" {{ $detail->disposition==$disp ? 'selected' : '' }}>{{ $disp }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bukti (Foto)</label>
                                <input type="file" name="details[{{ $i }}][evidence]" class="form-control">
                                @if($detail->evidence)
                                    <small>File saat ini: <a href="{{ asset('storage/'.$detail->evidence) }}" target="_blank">Lihat</a></small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label>Keterangan</label>
                                <textarea name="details[{{ $i }}][remark]" class="form-control">{{ $detail->remark }}</textarea>
                            </div>

                            <!-- <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button> -->
                        </div>
                    @endforeach
                </div>

                <!-- <button type="button" id="add-detail" class="btn btn-outline-secondary">+ Tambah Detail</button> -->
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailIndex = {{ $report->details->count() }};

    document.getElementById('add-detail').addEventListener('click', function() {
        const container = document.getElementById('detail-container');
        const template = container.querySelector('.detail-item').cloneNode(true);

        template.querySelectorAll('input, textarea, select').forEach(input => input.value='');
        template.querySelectorAll('input, textarea, select').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${detailIndex}]`);
        });

        container.appendChild(template);
        detailIndex++;
    });

    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-detail')){
            const item = e.target.closest('.detail-item');
            if(document.querySelectorAll('.detail-item').length > 1){
                item.remove();
            } else {
                alert('Harus ada minimal satu detail.');
            }
        }
    });
});
</script>
@endsection
