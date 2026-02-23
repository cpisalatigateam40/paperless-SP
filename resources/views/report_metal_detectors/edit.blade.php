@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Metal Detector Adonan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_metal_detectors.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
                </div>
                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                </div>

                <hr>
                <h5>Detail Pemeriksaan</h5>
                <div id="details ">
                    @foreach($details as $i => $detail)
                    <div class="card p-3 mb-3 mt-5">
                        <div class="mb-3">
                            <label>Produk</label>
                            <select name="details[{{ $i }}][product_uuid]" class="form-control" required>
                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}"
                                    {{ $detail->product_uuid == $product->uuid ? 'selected' : '' }}>
                                    {{ $product->product_name }} - {{ $product->nett_weight }} g
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Jam</label>
                            <input type="time" name="details[{{ $i }}][hour]" class="form-control"
                                value="{{ $detail->hour }}" required>
                        </div>
                        <div class="mb-3">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                value="{{ $detail->production_code }}" required>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label>Hasil Deteksi Fe 1.5 mm</label>
                                <select name="details[{{ $i }}][result_fe]" class="form-control" required>
                                    <option value="√" {{ $detail->result_fe == '√' ? 'selected' : '' }}>√</option>
                                    <option value="x" {{ $detail->result_fe == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label>Hasil Deteksi Non Fe 1.5 mm</label>
                                <select name="details[{{ $i }}][result_non_fe]" class="form-control" required>
                                    <option value="√" {{ $detail->result_non_fe == '√' ? 'selected' : '' }}>√</option>
                                    <option value="x" {{ $detail->result_non_fe == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label>Hasil Deteksi SUS 316 2.5 mm</label>
                                <select name="details[{{ $i }}][result_sus316]" class="form-control" required>
                                    <option value="√" {{ $detail->result_sus316 == '√' ? 'selected' : '' }}>√</option>
                                    <option value="x" {{ $detail->result_sus316 == 'x' ? 'selected' : '' }}>x</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Hasil Verifikasi MD Loma</label>
                            <select name="details[{{ $i }}][verif_loma]" class="form-control" required>
                                <option value="√" {{ $detail->verif_loma == '√' ? 'selected' : '' }}>√</option>
                                <option value="x" {{ $detail->verif_loma == 'x' ? 'selected' : '' }}>x</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea name="details[{{ $i }}][notes]"
                                class="form-control">{{ $detail->notes }}</textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Ketidaksesuaian</label>
                                <input type="text" name="details[{{ $i }}][nonconformity]" class="form-control"
                                    value="{{ $detail->nonconformity }}">
                            </div>
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <input type="text" name="details[{{ $i }}][corrective_action]" class="form-control"
                                    value="{{ $detail->corrective_action }}">
                            </div>
                            <div class="col-md-4">
                                <label>Verifikasi Setelah Tindakan Koreksi</label>
                                <select name="details[{{ $i }}][verif_after_correct]" class="form-control">
                                    <option value="√" {{ $detail->verif_after_correct == '√' ? 'selected' : '' }}>√
                                    </option>
                                    <option value="x" {{ $detail->verif_after_correct == 'x' ? 'selected' : '' }}>x
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Hapus
                            Detail</button> -->
                    </div>
                    @endforeach
                </div>

                <!-- <button type="button" class="btn btn-outline-secondary" onclick="addDetail()">+ Tambah Detail</button> -->
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('report_metal_detectors.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let detailIndex = {
    {
        count($details)
    }
};

function addDetail() {
    let html = `
        <div class="card p-3 mb-3">
            <div class="mb-3">
                <label>Produk</label>
                <select name="details[${detailIndex}][product_uuid]" class="form-control" required>
                    @foreach($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Jam</label>
                <input type="time" name="details[${detailIndex}][hour]" class="form-control"
                       value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
            </div>
            <div class="mb-3">
                <label>Kode Produksi</label>
                <input type="text" name="details[${detailIndex}][production_code]" class="form-control" required>
            </div>
            <div class="row">
                <div class="mb-3 col-md-4">
                    <label>Hasil Deteksi Fe 1.5 mm</label>
                    <select name="details[${detailIndex}][result_fe]" class="form-control" required>
                        <option value="√">√</option><option value="x">x</option>
                    </select>
                </div>
                <div class="mb-3 col-md-4">
                    <label>Hasil Deteksi Non Fe 1.5 mm</label>
                    <select name="details[${detailIndex}][result_non_fe]" class="form-control" required>
                        <option value="√">√</option><option value="x">x</option>
                    </select>
                </div>
                <div class="mb-3 col-md-4">
                    <label>Hasil Deteksi SUS 316 2.5 mm</label>
                    <select name="details[${detailIndex}][result_sus316]" class="form-control" required>
                        <option value="√">√</option><option value="x">x</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Hasil Verifikasi MD Loma</label>
                <select name="details[${detailIndex}][verif_loma]" class="form-control" required>
                    <option value="√">√</option><option value="x">x</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <textarea name="details[${detailIndex}][notes]" class="form-control"></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Ketidaksesuaian</label>
                    <input type="text" name="details[${detailIndex}][nonconformity]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Tindakan Koreksi</label>
                    <input type="text" name="details[${detailIndex}][corrective_action]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Verifikasi Setelah Tindakan Koreksi</label>
                    <select name="details[${detailIndex}][verif_after_correct]" class="form-control" required>
                        <option value="√">√</option><option value="x">x</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Hapus Detail</button>
        </div>`;
    document.getElementById('details').insertAdjacentHTML('beforeend', html);
    detailIndex++;
}
</script>
@endsection