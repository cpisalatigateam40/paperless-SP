@csrf

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@php
    // Pembuat input angka 2 desimal untuk tabel standar.
    // $min0 = true -> tidak boleh negatif (dipakai untuk Set Time).
    $in = function (string $key, string $field, string $placeholder, bool $min0 = false) use ($values) {
        $row = $values[$key] ?? null;
        $val = old("parameters.$key.$field", $row?->$field);

        return '<input type="number" step="0.01"' . ($min0 ? ' min="0"' : '')
            . ' class="form-control form-control-sm"'
            . ' name="parameters[' . $key . '][' . $field . ']"'
            . ' placeholder="' . e($placeholder) . '"'
            . ' value="' . e($val) . '">';
    };
@endphp

<div class="row mb-4">
    <div class="col-md-6">
        <label>Produk</label>
        @if($master)
            <input type="text" class="form-control" value="{{ $master->product->product_name ?? '-' }}" readonly>
        @else
            <select name="product_uuid" class="form-control select2-product" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $product)
                <option value="{{ $product->uuid }}" @selected(old('product_uuid') == $product->uuid)>
                    {{ $product->product_name }}
                </option>
                @endforeach
            </select>
        @endif
    </div>
    <div class="col-md-3">
        <label>Initial Temp Min (°C)</label>
        <input type="number" step="0.01" name="initial_temp_min" class="form-control"
            placeholder="Mis. 10"
            value="{{ old('initial_temp_min', $master?->initial_temp_min) }}">
    </div>
    <div class="col-md-3">
        <label>Initial Temp Max (°C)</label>
        <input type="number" step="0.01" name="initial_temp_max" class="form-control"
            placeholder="Kosongkan jika bukan range"
            value="{{ old('initial_temp_max', $master?->initial_temp_max) }}">
    </div>
</div>

<div class="alert alert-info d-flex align-items-start" role="alert">
    <i class="bx bx-info-circle me-2 mt-1"></i>
    <div>
        <strong>Catatan pengisian parameter:</strong>
        <ul class="mb-0 mt-1">
            <li>Kolom <strong>Min</strong> dan <strong>Max</strong> dipakai untuk parameter yang punya rentang nilai (misal suhu 55–60°C, waktu 25–30 menit).</li>
            <li>Kalau nilainya <strong>tidak berupa range</strong> (cuma satu angka pasti), isi kolom <strong>Min</strong> saja dan biarkan <strong>Max</strong> kosong.</li>
            <li>Kolom yang dikosongi otomatis tidak ditampilkan sebagai range di form laporan.</li>
            <li>Untuk <strong>Drying</strong>, Set Temp diisi per zona (Hot zone dan Cold zone).</li>
        </ul>
    </div>
</div>

<h6>Standar per Parameter</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="text-center">
            <tr>
                <th rowspan="2" class="align-middle">Parameter</th>
                <th colspan="2">Set Temp (°C)</th>
                <th colspan="2">Set Time (menit)</th>
                <th colspan="2">Std CT Prod. (°C)</th>
            </tr>
            <tr>
                <th>Min</th><th>Max</th>
                <th>Min</th><th>Max</th>
                <th>Min</th><th>Max</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parameters as $key => $label)

            @if($key === 'drying')
            {{-- Drying: Hot zone --}}
            <tr>
                <td>{{ $label }} <small class="text-muted">(Hot zone)</small></td>
                <td>{!! $in($key, 'set_temp_min', 'Mis. 60') !!}</td>
                <td>{!! $in($key, 'set_temp_max', 'Opsional') !!}</td>
                <td>{!! $in($key, 'set_time_min', 'Mis. 30', true) !!}</td>
                <td>{!! $in($key, 'set_time_max', 'Opsional', true) !!}</td>
                <td>{!! $in($key, 'std_ct_min', 'Mis. 20') !!}</td>
                <td>{!! $in($key, 'std_ct_max', 'Opsional') !!}</td>
            </tr>
            {{-- Drying: Cold zone --}}
            <tr>
                <td>{{ $label }} <small class="text-muted">(Cold zone)</small></td>
                <td>{!! $in($key, 'cold_zone_temp_min', 'Mis. 30') !!}</td>
                <td>{!! $in($key, 'cold_zone_temp_max', 'Opsional') !!}</td>
                <td colspan="4" class="bg-light text-muted text-center">
                    <small>Set Time &amp; Std CT mengikuti baris Hot zone</small>
                </td>
            </tr>
            @else
            <tr>
                <td>{{ $label }}</td>
                <td>{!! $in($key, 'set_temp_min', 'Mis. 90') !!}</td>
                <td>{!! $in($key, 'set_temp_max', 'Opsional') !!}</td>
                <td>{!! $in($key, 'set_time_min', 'Mis. 30', true) !!}</td>
                <td>{!! $in($key, 'set_time_max', 'Opsional', true) !!}</td>
                <td>{!! $in($key, 'std_ct_min', 'Mis. 72') !!}</td>
                <td>{!! $in($key, 'std_ct_max', 'Opsional') !!}</td>
            </tr>
            @endif

            @endforeach
        </tbody>
    </table>
</div>

<a href="{{ route('master_waterbaths.index') }}" class="btn btn-secondary mt-3">Kembali</a>
<button type="submit" class="btn btn-success mt-3">Simpan</button>