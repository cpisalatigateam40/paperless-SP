@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Kinerja Metal Detector Adonan', 'url' => route('report_metal_detectors.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Verifikasi Kinerja Metal Detector Adonan</h4>
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
                <!-- <div class="mb-3">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control" required>
                        <option value="">-- Pilih Section --</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}"
                            {{ $section->uuid == $report->section_uuid ? 'selected' : '' }}>
                            {{ $section->section_name }}
                        </option>
                        @endforeach
                    </select>
                </div> -->

                <hr>
                <h5>Detail Pemeriksaan</h5>
                <div id="details ">
                    @foreach($details as $i => $detail)
                    <div class="card p-3 mt-5" style="margin-bottom: 7rem;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Produk</label>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control" required>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        {{ $detail->product_uuid == $product->uuid ? 'selected' : '' }}>
                                        {{ $product->product_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gramase</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="details[{{ $i }}][gramase]" 
                                    class="form-control"
                                    value="{{ $detail->gramase }}"
                                    placeholder="Masukkan gramase">
                            </div>
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
                                <label class="form-label d-block">Hasil Deteksi Fe 1.5 mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_fe]" value="√"
                                        class="form-check-input" id="result_fe_ok_{{ $i }}" required
                                        {{ $detail->result_fe == '√' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_fe_ok_{{ $i }}">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_fe]" value="x"
                                        class="form-check-input" id="result_fe_x_{{ $i }}" required
                                        {{ $detail->result_fe == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_fe_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label d-block">Hasil Deteksi Non Fe 2 mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_non_fe]" value="√"
                                        class="form-check-input" id="result_non_fe_ok_{{ $i }}" required
                                        {{ $detail->result_non_fe == '√' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_non_fe_ok_{{ $i }}">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_non_fe]" value="x"
                                        class="form-check-input" id="result_non_fe_x_{{ $i }}" required
                                        {{ $detail->result_non_fe == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_non_fe_x_{{ $i }}">x</label>
                                </div>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label d-block">Hasil Deteksi SUS 316 2.5 mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_sus316]" value="√"
                                        class="form-check-input" id="result_sus316_ok_{{ $i }}" required
                                        {{ $detail->result_sus316 == '√' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_sus316_ok_{{ $i }}">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][result_sus316]" value="x"
                                        class="form-check-input" id="result_sus316_x_{{ $i }}" required
                                        {{ $detail->result_sus316 == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="result_sus316_x_{{ $i }}">x</label>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="mb-3">
                            <label>Hasil Verifikasi MD Loma</label>
                            <select name="details[{{ $i }}][verif_loma]" class="form-control" required>
                                <option value="√" {{ $detail->verif_loma == '√' ? 'selected' : '' }}>√</option>
                                <option value="x" {{ $detail->verif_loma == 'x' ? 'selected' : '' }}>x</option>
                            </select>
                        </div> -->
                        

                        <div class="row mb-3">
                            <!-- <div class="col-md-4">
                                <label>Ketidaksesuaian</label>
                                <input type="text" name="details[{{ $i }}][nonconformity]" class="form-control"
                                    value="{{ $detail->nonconformity }}">
                            </div> -->
                            <div class="col-md-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][verif_after_correct]" value="√"
                                        class="form-check-input" id="verif_after_correct_ok_{{ $i }}"
                                        {{ $detail->verif_after_correct == '√' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="verif_after_correct_ok_{{ $i }}">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $i }}][verif_after_correct]" value="x"
                                        class="form-check-input" id="verif_after_correct_x_{{ $i }}"
                                        {{ $detail->verif_after_correct == 'x' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="verif_after_correct_x_{{ $i }}">x</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <input type="text" name="details[{{ $i }}][corrective_action]" class="form-control"
                                    value="{{ $detail->corrective_action }}">
                            </div>
                            <div class="col-md-4">
                                <label>Keterangan</label>
                                <textarea name="details[{{ $i }}][notes]"
                                    class="form-control" rows="1">{{ $detail->notes }}</textarea>
                            </div>
                        </div>

                        <!-- <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Hapus
                            Detail</button> -->
                    </div>
                    @endforeach
                </div>

                <div class="form-group mt-5 mb-5">
                    <label>Notes</label>
                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                        placeholder="Masukkan catatan apabila diperlukan">{{ old('notes', $report->notes) }}</textarea>
                </div>

                <!-- <button type="button" class="btn btn-outline-secondary" onclick="addDetail()">+ Tambah Detail</button> -->
                 <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-success">Update</button>
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