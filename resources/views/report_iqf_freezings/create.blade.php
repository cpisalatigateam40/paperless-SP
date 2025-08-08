@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Report Verifikasi Pembekuan IQF</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_iqf_freezings.store') }}">
                @csrf
                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control">
                </div>

                <hr>
                <h5 class="mt-5">Detail Produk</h5>
                <div id="details">
                    <div class="detail-item mb-3 border p-2">
                        <div class="mb-2">
                            <label>Nama Produk</label>
                            <select name="details[0][product_uuid]" class="form-control"
                                onchange="updateBestBefore(this, 0)">
                                <option value="">-- Pilih Produk--</option>
                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}">
                                    {{ $product->product_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[0][production_code]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Best Before</label>
                            <input type="date" name="details[0][best_before]" class="form-control" readonly>
                        </div>
                        <div class="mb-2">
                            <label>Suhu Produk Sebelum IQF (°C)</label>
                            <input type="number" step="0.01" name="details[0][product_temp_before_iqf]"
                                class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Jam Mulai Pembekuan</label>
                            <input type="time" name="details[0][freezing_start_time]" class="form-control"
                                value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                        </div>
                        <div class="mb-2">
                            <label>Lama Pembekuan (menit)</label>
                            <input type="number" name="details[0][freezing_duration]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Suhu Ruang IQF</label>
                            <input type="number" name="details[0][room_temperature]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Suhu Suction IQF</label>
                            <input type="number" name="details[0][suction_temperature]" class="form-control">
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-detail">Tambah Detail</button>

                <button class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;
document.getElementById('add-detail').addEventListener('click', function() {
    const details = document.getElementById('details');
    const template = `
        <div class="detail-item mb-3 border p-2">
            <div class="mb-2">
                <label>Nama Produk</label>
                <select name="details[${detailIndex}][product_uuid]" class="form-control" onchange="updateBestBefore(this, ${detailIndex})">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->uuid }}" data-shelf-life="{{ $product->shelf_life }}">
                            {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2">
                <label>Kode Produksi</label>
                <input type="text" name="details[${detailIndex}][production_code]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Best Before</label>
                <input type="date" name="details[${detailIndex}][best_before]" class="form-control" readonly>
            </div>
            <div class="mb-2">
                <label>Suhu Produk Sebelum IQF (°C)</label>
                <input type="number" step="0.01" name="details[${detailIndex}][product_temp_before_iqf]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Jam Mulai Pembekuan</label>
                <input type="time" name="details[${detailIndex}][freezing_start_time]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="mb-2">
                <label>Lama Pembekuan (menit)</label>
                <input type="number" name="details[${detailIndex}][freezing_duration]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Suhu Ruang IQF</label>
                <input type="number" name="details[${detailIndex}][room_temperature]" class="form-control">
            </div>
            <div class="mb-2">
                <label>Suhu Suction IQF</label>
                <input type="number" name="details[${detailIndex}][suction_temperature]" class="form-control">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus Detail</button>
        </div>`;

    details.insertAdjacentHTML('beforeend', template);
    detailIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        e.target.closest('.detail-item').remove();
    }
});

function updateBestBefore(select, index) {
    const selected = select.options[select.selectedIndex];
    const shelfLife = selected.getAttribute('data-shelf-life');

    if (shelfLife) {
        const today = new Date();
        today.setMonth(today.getMonth() + parseInt(shelfLife));

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        const bestBefore = `${year}-${month}-${day}`;
        document.querySelector(`input[name="details[${index}][best_before]"]`).value = bestBefore;
    }
}
</script>
@endsection