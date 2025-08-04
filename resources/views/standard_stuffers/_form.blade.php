<div class="mb-3">
    <label for="product_uuid">Product</label>
    <select name="product_uuid" class="form-control">
        <option value="">-- Pilih Product --</option>
        @foreach($products as $product)
        <option value="{{ $product->uuid }}"
            {{ old('product_uuid', $stuffer->product_uuid ?? '') == $product->uuid ? 'selected' : '' }}>
            {{ $product->product_name }} {{ $product->nett_weight }}
        </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="mb-3 col-md-6">
        <label for="long_min">Panjang Min (mm)</label>
        <input type="number" name="long_min" class="form-control"
            value="{{ old('long_min', $stuffer->long_min ?? '') }}">
    </div>

    <div class="mb-3 col-md-6">
        <label for="long_max">Panjang Max (mm)</label>
        <input type="number" name="long_max" class="form-control"
            value="{{ old('long_max', $stuffer->long_max ?? '') }}">
    </div>
</div>


<div class="mb-3">
    <label for="diameter">Diameter (mm)</label>
    <input type="number" name="diameter" class="form-control" value="{{ old('diameter', $stuffer->diameter ?? '') }}">
</div>

<div class="row">
    <div class="mb-3 col-md-6">
        <label for="weight_min">Berat Min (gr)</label>
        <input type="number" name="weight_min" class="form-control"
            value="{{ old('weight_min', $stuffer->weight_min ?? '') }}">
    </div>

    <div class="mb-3 col-md-6">
        <label for="weight_max">Berat Max (gr)</label>
        <input type="number" name="weight_max" class="form-control"
            value="{{ old('weight_max', $stuffer->weight_max ?? '') }}">
    </div>
</div>