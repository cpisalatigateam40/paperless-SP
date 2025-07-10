@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report: {{ $report->date }} - Shift {{ $report->shift }}</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_iqf_freezings.details.store', $report->uuid) }}">
                @csrf
                <div class="mb-3">
                    <label>Nama Produk</label>
                    <select name="product_uuid" class="form-control" onchange="updateBestBefore(this)" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}">
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
                    <label>Best Before</label>
                    <input type="date" name="best_before" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label>Suhu Produk Sebelum IQF (°C)</label>
                    <input type="number" step="0.01" name="product_temp_before_iqf" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Jam Mulai Pembekuan</label>
                    <input type="time" name="freezing_start_time" class="form-control"
                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                </div>
                <div class="mb-3">
                    <label>Lama Pembekuan (menit)</label>
                    <input type="number" name="freezing_duration" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Suhu Ruang IQF</label>
                    <input type="number" name="room_temperature" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Suhu Suction IQF</label>
                    <input type="number" name="suction_temperature" class="form-control">
                </div>
                <button class="btn btn-success">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function updateBestBefore(select) {
    const selected = select.options[select.selectedIndex];
    const shelfLife = selected.getAttribute('data-shelf-life');

    if (shelfLife) {
        const today = new Date();
        today.setMonth(today.getMonth() + parseInt(shelfLife));

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        const bestBefore = `${year}-${month}-${day}`;
        document.querySelector('input[name="best_before"]').value = bestBefore;
    }
}
</script>
@endsection