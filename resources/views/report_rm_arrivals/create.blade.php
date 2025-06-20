@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Tambah Laporan Kedatangan Bahan Baku</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_rm_arrivals.store') }}">
                @csrf

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <h5>Detail Pemeriksaan</h5>
                <div id="detail-container">
                    <div class="detail-row mb-3 p-3 border rounded bg-light">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[0][raw_material_uuid]" class="form-control" required>
                                    @foreach ($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}">{{ $material->material_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jam</label>
                                <input type="time" name="details[0][time]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Suhu (°C)</label>
                                <input type="number" step="0.1" name="details[0][temperature]" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Kemasan</label>
                                <select name="details[0][packaging_condition]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sensorik</label>
                                <select name="details[0][sensorial_condition]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Problem</label>
                                <textarea name="details[0][problem]" class="form-control" rows="2" placeholder="Jika ada masalah, tulis di sini..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tindakan Koreksi</label>
                                <textarea name="details[0][corrective_action]" class="form-control" rows="2" placeholder="Langkah yang dilakukan..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary mt-3">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection