@php
$isEdit = isset($steamerStandard);
@endphp

<div class="alert alert-info d-flex align-items-start" role="alert">
    <i class="bx bx-info-circle me-2 mt-1"></i>
    <div>
        <strong>Catatan pengisian parameter:</strong>
        <ul class="mb-0 mt-1">
            <li>Kolom <strong>Min</strong> dan <strong>Max</strong> dipakai untuk parameter yang punya rentang nilai (misal suhu 55–60°C).</li>
            <li>Kalau nilainya <strong>tidak berupa range</strong> (cuma satu angka pasti), isi kolom <strong>Min</strong> saja dan biarkan <strong>Max</strong> kosong.</li>
            <li>Kolom yang dikosongi otomatis tidak ditampilkan sebagai range di form laporan.</li>
        </ul>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Produk</label>
    <select name="product_uuid" class="form-select @error('product_uuid') is-invalid @enderror form-control" required>
        <option value="">-- Pilih Produk --</option>
        @foreach ($products as $product)
        <option value="{{ $product->uuid }}"
            {{ old('product_uuid', $isEdit ? $steamerStandard->product_uuid : '') == $product->uuid ? 'selected' : '' }}>
            {{ $product->product_name }}
        </option>
        @endforeach
    </select>
    @error('product_uuid')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Suhu Ruang Min (°C)</label>
        <input type="number" step="0.01" name="room_temp_min"
            class="form-control @error('room_temp_min') is-invalid @enderror"
            value="{{ old('room_temp_min', $isEdit ? $steamerStandard->room_temp_min : '') }}" placeholder="mis: 6.5">
        @error('room_temp_min')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Suhu Ruang Max (°C)</label>
        <input type="number" step="0.01" name="room_temp_max"
            class="form-control @error('room_temp_max') is-invalid @enderror"
            value="{{ old('room_temp_max', $isEdit ? $steamerStandard->room_temp_max : '') }}" placeholder="mis: 12.5">
        @error('room_temp_max')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Setup Time Min (menit)</label>
        <input type="number" name="setup_time_min" class="form-control @error('setup_time_min') is-invalid @enderror"
            value="{{ old('setup_time_min', $isEdit ? $steamerStandard->setup_time_min : '') }}" placeholder="mis: 6">
        @error('setup_time_min')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Setup Time Max (menit)</label>
        <input type="number" name="setup_time_max" class="form-control @error('setup_time_max') is-invalid @enderror"
            value="{{ old('setup_time_max', $isEdit ? $steamerStandard->setup_time_max : '') }}" placeholder="mis: 12">
        @error('setup_time_max')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Core Temp Min (°C)</label>
        <input type="number" step="0.01" name="core_temp_min"
            class="form-control @error('core_temp_min') is-invalid @enderror"
            value="{{ old('core_temp_min', $isEdit ? $steamerStandard->core_temp_min : '') }}" placeholder="mis: 6.5">
        @error('core_temp_min')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Core Temp Max (°C)</label>
        <input type="number" step="0.01" name="core_temp_max"
            class="form-control @error('core_temp_max') is-invalid @enderror"
            value="{{ old('core_temp_max', $isEdit ? $steamerStandard->core_temp_max : '') }}" placeholder="mis: 12.5">
        @error('core_temp_max')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('steamer-standards.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>