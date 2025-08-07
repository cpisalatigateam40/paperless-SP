<div class="mb-3">
    <label for="product_uuid" class="form-label">Produk</label>
    <select name="product_uuid" id="product_uuid" class="form-select form-control" required>
        <option value="">Pilih Produk</option>
        @foreach($products as $product)
        <option value="{{ $product->uuid }}"
            {{ old('product_uuid', $standard->product_uuid ?? '') == $product->uuid ? 'selected' : '' }}>
            {{ $product->product_name }} {{ $product->nett_weight }}
        </option>
        @endforeach
    </select>
</div>

<div id="steps-container">
    <div class="step-group border rounded p-3 mb-3">
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <label>Step Proses</label>
                <select name="steps[0][process_step_uuid]" class="form-select form-control" required>
                    <option value="">Pilih Step</option>
                    @foreach($steps as $step)
                    <option value="{{ $step->uuid }}">{{ $step->process_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <label>ST Min (°C)</label>
                <input type="number" step="0.01" name="steps[0][st_min]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>ST Max (°C)</label>
                <input type="number" step="0.01" name="steps[0][st_max]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Time (menit)</label>
                <input type="number" name="steps[0][time_minute]" class="form-control">
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-3">
                <label>RH Min (%)</label>
                <input type="number" step="0.01" name="steps[0][rh_min]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>RH Max (%)</label>
                <input type="number" step="0.01" name="steps[0][rh_max]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>CT Min (°C)</label>
                <input type="number" step="0.01" name="steps[0][ct_min]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>CT Max (°C)</label>
                <input type="number" step="0.01" name="steps[0][ct_max]" class="form-control">
            </div>
        </div>

        <button type="button" class="btn btn-danger btn-sm mt-3 remove-step">Hapus Step</button>
    </div>
</div>