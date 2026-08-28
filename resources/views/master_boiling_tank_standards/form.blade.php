@extends('layouts.app')

@section('content')
<form action="{{ $isEdit ? route('master_boiling_tank_standards.update', $standard->uuid) : route('master_boiling_tank_standards.store') }}"
      method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header fw-bold">Standar Suhu &amp; Berat</div>
        <div class="card-body">
            <div class="alert alert-info d-flex gap-2 mb-3">
                <i class="fas fa-circle-info mt-1"></i>
                <div>
                    <strong>Cara mengisi:</strong> Setiap standar punya kolom <em>Min</em> dan <em>Maks</em>.
                    <ul class="mb-0 mt-1">
                        <li>Jika standar berupa <strong>rentang</strong> (misal suhu 75°C - 85°C), isi <strong>Min</strong> dan <strong>Maks</strong> sesuai batas bawah dan atasnya.</li>
                        <li>Jika standar berupa <strong>satu nilai tetap</strong> (misal berat matang harus 12 gr, bukan rentang), cukup isi kolom <strong>Min</strong> saja dan biarkan <strong>Maks</strong> kosong.</li>
                    </ul>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nama Produk</label>
                    <select name="product_uuid" class="form-select form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                @selected(old('product_uuid', $standard->product_uuid ?? null) == $product->uuid)>
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_uuid')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 fw-semibold">Suhu Tangki I (°C)</div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Min</label>
                    <input type="number" step="0.01" name="suhu_tangki_1_min" class="form-control" placeholder="mis: 75"
                           value="{{ old('suhu_tangki_1_min', $standard->suhu_tangki_1_min ?? '') }}" required>
                    @error('suhu_tangki_1_min')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Maks <small class="text-muted">(opsional)</small></label>
                    <input type="number" step="0.01" name="suhu_tangki_1_max" class="form-control" placeholder="mis: 85"
                           value="{{ old('suhu_tangki_1_max', $standard->suhu_tangki_1_max ?? '') }}">
                    @error('suhu_tangki_1_max')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 fw-semibold">Suhu Tangki II (°C)</div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Min</label>
                    <input type="number" step="0.01" name="suhu_tangki_2_min" class="form-control" placeholder="mis: 85"
                           value="{{ old('suhu_tangki_2_min', $standard->suhu_tangki_2_min ?? '') }}" required>
                    @error('suhu_tangki_2_min')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Maks <small class="text-muted">(opsional)</small></label>
                    <input type="number" step="0.01" name="suhu_tangki_2_max" class="form-control" placeholder="mis: 95"
                           value="{{ old('suhu_tangki_2_max', $standard->suhu_tangki_2_max ?? '') }}">
                    @error('suhu_tangki_2_max')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 fw-semibold">Berat Mentah (gr)</div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Min</label>
                    <input type="number" step="0.01" name="berat_mentah_min" class="form-control" placeholder="mis: 11"
                           value="{{ old('berat_mentah_min', $standard->berat_mentah_min ?? '') }}" required>
                    @error('berat_mentah_min')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Maks <small class="text-muted">(opsional)</small></label>
                    <input type="number" step="0.01" name="berat_mentah_max" class="form-control" placeholder="mis: 12"
                           value="{{ old('berat_mentah_max', $standard->berat_mentah_max ?? '') }}">
                    @error('berat_mentah_max')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 fw-semibold">Actual Core Temp (°C)</div>
                <div class="col-6 col-md-6 ">
                    <label class="form-label small mb-1">Min</label>
                    <input type="number" step="0.01" name="actual_core_temp_min" class="form-control" placeholder="mis: 12"
                        value="{{ old('actual_core_temp_min', $standard->actual_core_temp_min ?? '') }}" required>
                    @error('actual_core_temp_min')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-6 ">
                    <label class="form-label small mb-1">Maks <small class="text-muted">(opsional)</small></label>
                    <input type="number" step="0.01" name="actual_core_temp_max" class="form-control" placeholder="mis: 12"
                        value="{{ old('actual_core_temp_max', $standard->actual_core_temp_max ?? '') }}">
                    @error('actual_core_temp_max')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 fw-semibold">Berat Matang (gr)</div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Min</label>
                    <input type="number" step="0.01" name="berat_matang_min" class="form-control" placeholder="mis: 12"
                           value="{{ old('berat_matang_min', $standard->berat_matang_min ?? '') }}" required>
                    @error('berat_matang_min')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label small mb-1">Maks <small class="text-muted">(opsional)</small></label>
                    <input type="number" step="0.01" name="berat_matang_max" class="form-control" placeholder="mis: 12"
                           value="{{ old('berat_matang_max', $standard->berat_matang_max ?? '') }}">
                    @error('berat_matang_max')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-5">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('master_boiling_tank_standards.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection