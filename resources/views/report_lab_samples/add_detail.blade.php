@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail ke Report Tanggal {{ $report->date }} (Shift {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_lab_samples.details.store', $report->uuid) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label>Nama Produk</label>
                    <select name="product_uuid" class="form-control" required onchange="updateExpiredDate(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                            data-created-at="{{ $product->created_at }}">
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Best Before</label>
                    <input type="date" name="best_before" class="form-control" readonly required>
                </div>
                <div class="mb-2">
                    <label>Jumlah</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>

<script>
function updateExpiredDate(select) {
    let selectedOption = select.options[select.selectedIndex];
    let shelfLife = selectedOption.getAttribute('data-shelf-life');
    let createdAt = selectedOption.getAttribute('data-created-at');
    if (shelfLife && createdAt) {
        let createdDate = new Date(createdAt);
        createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

        let year = createdDate.getFullYear();
        let month = String(createdDate.getMonth() + 1).padStart(2, '0');
        let day = String(createdDate.getDate()).padStart(2, '0');

        let expiredDateStr = `${year}-${month}-${day}`;
        document.querySelector('input[name="best_before"]').value = expiredDateStr;
    }
}
</script>
@endsection