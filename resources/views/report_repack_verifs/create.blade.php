@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Form Verifikasi Repack Produk</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_repack_verifs.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>

                <hr>
                <h5>Details</h5>
                <div id="details-container">
                    <div class="detail-item mb-3 border p-2">
                        <label>Nama Produk</label>
                        <select name="details[0][product_uuid]" class="form-control"
                            onchange="updateExpiredDate(this, 0)">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}"
                                data-created-at="{{ $product->created_at }}">
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                        <label>Kode Produksi</label>
                        <input type="text" name="details[0][production_code]" class="form-control">
                        <label>Expired Date</label>
                        <input type="date" name="details[0][expired_date]" class="form-control" readonly>
                        <label>Alasan Repack</label>
                        <input type="text" name="details[0][reason]" class="form-control">
                        <label>Keterangan</label>
                        <input type="text" name="details[0][notes]" class="form-control">
                    </div>
                </div>

                <button type="button" class="btn btn-secondary mb-3" onclick="addDetail()">Tambah Detail</button>
                <br>

                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
</div>

<script>
let detailIndex = 1;

function addDetail() {
    let container = document.getElementById('details-container');
    let html = `
        <div class="detail-item mb-3 border p-2">
            <label>Product</label>
            <select name="details[${detailIndex}][product_uuid]" class="form-control" onchange="updateExpiredDate(this, ${detailIndex})" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $product)
                    <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}" data-created-at="{{ $product->created_at }}">
                        {{ $product->product_name }}
                    </option>
                @endforeach
            </select>
            <label>Production Code</label>
            <input type="text" name="details[${detailIndex}][production_code]" class="form-control">
            <label>Expired Date</label>
            <input type="date" name="details[${detailIndex}][expired_date]" class="form-control" readonly>
            <label>Reason</label>
            <input type="text" name="details[${detailIndex}][reason]" class="form-control">
            <label>Notes</label>
            <input type="text" name="details[${detailIndex}][notes]" class="form-control">
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    detailIndex++;
}

function updateExpiredDate(select, index) {
    let selectedOption = select.options[select.selectedIndex];
    let shelfLife = selectedOption.getAttribute('data-shelf-life');
    let createdAt = selectedOption.getAttribute('data-created-at');
    if (shelfLife && createdAt) {
        let createdDate = new Date(createdAt);
        createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

        let year = createdDate.getFullYear();
        let month = String(createdDate.getMonth() + 1).padStart(2, '0'); // Month: 0-indexed
        let day = String(createdDate.getDate()).padStart(2, '0');

        let expiredDateStr = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][expired_date]"]`).value = expiredDateStr;
    }
}
</script>
@endsection