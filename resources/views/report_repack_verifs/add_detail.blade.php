@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report Tanggal {{ $report->date }} (Area: {{ $report->area->name ?? '-' }})</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_repack_verifs.details.store', $report->uuid) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Produk</label>
                    <select name="product_uuid" class="form-control" onchange="updateExpiredDate(this)" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                            data-created-at="{{ $product->created_at }}">
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Expired Date</label>
                    <input type="date" name="expired_date" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Alasan Repack</label>
                    <input type="text" name="reason" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="notes" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Tambah Detail</button>
                <a href="{{ route('report_repack_verifs.index') }}" class="btn btn-secondary">Kembali</a>
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
        document.querySelector('input[name="expired_date"]').value = expiredDateStr;
    }
}
</script>
@endsection