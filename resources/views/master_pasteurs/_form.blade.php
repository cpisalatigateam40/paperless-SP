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
            <li>Kolom <strong>Min</strong> dan <strong>Max</strong> dipakai untuk parameter yang punya rentang nilai (misal suhu 55–60°C).</li>
            <li>Kalau nilainya <strong>tidak berupa range</strong> (cuma satu angka pasti), isi kolom <strong>Min</strong> saja dan biarkan <strong>Max</strong> kosong.</li>
            <li>Kolom yang dikosongi otomatis tidak ditampilkan sebagai range di form laporan.</li>
        </ul>
    </div>
</div>

<h6>Standar per Step</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="text-center">
            <tr>
                <th rowspan="2" class="align-middle">Parameter</th>
                <th rowspan="2" class="align-middle">Set Time<br>(menit)</th>
                <th colspan="2">Set Water Temp (°C)</th>
                <th colspan="2">Set Pressure (Mpa)</th>
            </tr>
            <tr>
                <th>Min</th>
                <th>Max</th>
                <th>Min</th>
                <th>Max</th>
            </tr>
        </thead>
        <tbody>
            @foreach($steps as $order => $name)
            @php $v = $stepValues[$order] ?? null; @endphp
            <tr>
                <td>{{ $order }}. {{ $name }}</td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                        name="steps[{{ $order }}][set_time]"
                        placeholder="Mis. 5"
                        value="{{ old("steps.$order.set_time", $v?->set_time) }}">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm"
                        name="steps[{{ $order }}][water_temp_min]"
                        placeholder="Mis. 55"
                        value="{{ old("steps.$order.water_temp_min", $v?->water_temp_min) }}">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm"
                        name="steps[{{ $order }}][water_temp_max]"
                        placeholder="Opsional"
                        value="{{ old("steps.$order.water_temp_max", $v?->water_temp_max) }}">
                </td>
                <td>
                    <input type="number" step="0.001" min="0" class="form-control form-control-sm"
                        name="steps[{{ $order }}][pressure_min]"
                        placeholder="Mis. 0.350"
                        value="{{ old("steps.$order.pressure_min", $v?->pressure_min) }}">
                </td>
                <td>
                    <input type="number" step="0.001" min="0" class="form-control form-control-sm"
                        name="steps[{{ $order }}][pressure_max]"
                        placeholder="Opsional"
                        value="{{ old("steps.$order.pressure_max", $v?->pressure_max) }}">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<a href="{{ route('master_pasteurs.index') }}" class="btn btn-secondary mt-3">Kembali</a>
<button type="submit" class="btn btn-success mt-3">Simpan</button>