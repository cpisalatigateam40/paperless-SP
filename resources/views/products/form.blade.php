@csrf

<div class="mb-3">
    <label>Nama Produk</label>
    <input type="text" name="product_name" value="{{ old('product_name', $product->product_name ?? '') }}" class="form-control" required>
</div>

<div class="mb-3">
    <label>Merek</label>
    <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}" class="form-control">
</div>

<div class="mb-3">
    <label>Berat (Gram)</label>
    <input type="number" step="0.01" name="nett_weight" value="{{ old('nett_weight', $product->nett_weight ?? '') }}" class="form-control">
</div>

<div class="mb-3">
    <label>Kadaluwarsa (Bulan)</label>
    <input type="number" name="shelf_life" value="{{ old('shelf_life', $product->shelf_life ?? '') }}" class="form-control">
</div>

<button type="submit" class="btn btn-success">Save</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>

