@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_product_verifs.store') }}" method="POST">
        @csrf
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5 class="mb-0">Form Laporan Verifikasi Produk</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-3">
                        <label>Shift</label>
                        <input type="text" class="form-control" value="{{ getShift() }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL PRODUK --}}
        <div id="product-details-container">
            <div class="card shadow mb-3 product-detail">
                <div class="card-header d-flex justify-content-between">
                    <strong>Detail Produk</strong>
                    <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus</button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered text-center align-middle table-sm">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" style="vertical-align: middle">Jam</th>
                                <th rowspan="2" style="vertical-align: middle">Produk</th>
                                <th rowspan="2" style="vertical-align: middle">Kode Produksi</th>
                                <th rowspan="2" style="vertical-align: middle">Expired Date</th>
                                <th colspan="2">Standar Panjang (mm)</th>
                                <th colspan="2">Standar Berat (gr)</th>
                                <th rowspan="2">Diameter (mm)</th>
                            </tr>
                            <tr>
                                <th>Standar</th>
                                <th>Aktual</th>
                                <th>Standar</th>
                                <th>Aktual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++) <tr>
                                @if ($i == 0)
                                <td rowspan="5">
                                    <input type="time" name="details[0][jam]" class="form-control form-control-sm"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}" />
                                </td>
                                <td rowspan="5">
                                    <select name="details[0][product_uuid]" class="form-control form-control-sm"
                                        onchange="updateExpiredDate(this, 0)">
                                        <option value="">- pilih -</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->uuid }}"
                                            data-shelf-life="{{ $product->shelf_life }}"
                                            data-created-at="{{ \Carbon\Carbon::today()->toDateString() }}">
                                            {{ $product->product_name }} {{ $product->nett_weight }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td rowspan="5">
                                    <input type="text" name="details[0][production_code]"
                                        class="form-control form-control-sm" />
                                </td>
                                <td rowspan="5">
                                    <input type="date" name="details[0][expired_date]"
                                        class="form-control form-control-sm" />
                                </td>
                                <td rowspan="5">
                                    <input type="number" name="details[0][long_standard]"
                                        class="form-control form-control-sm" />
                                </td>
                                @endif

                                {{-- Panjang Aktual --}}
                                <td>
                                    <input type="number" step="any"
                                        name="details[0][measurements][{{ $i }}][length_actual]"
                                        class="form-control form-control-sm" />
                                </td>

                                @if ($i == 0)
                                <td rowspan="5">
                                    <input type="number" name="details[0][weight_standard]"
                                        class="form-control form-control-sm" />
                                </td>
                                @endif

                                {{-- Berat Aktual --}}
                                <td>
                                    <input type="number" step="any"
                                        name="details[0][measurements][{{ $i }}][weight_actual]"
                                        class="form-control form-control-sm" />
                                </td>

                                {{-- Diameter --}}
                                <td>
                                    <input type="number" step="any"
                                        name="details[0][measurements][{{ $i }}][diameter_actual]"
                                        class="form-control form-control-sm" />
                                </td>

                                <input type="hidden" name="details[0][measurements][{{ $i }}][sequence]"
                                    value="{{ $i + 1 }}">
                                </tr>
                                @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <button type="button" id="add-detail" class="btn btn-secondary btn-sm mb-3">+ Tambah Produk</button>
        <br>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;

document.getElementById('add-detail').addEventListener('click', function() {
    const container = document.getElementById('product-details-container');
    const original = container.querySelector('.product-detail');

    // Ambil hanya isi dari <tbody> pertama
    const tbody = original.querySelector('tbody');
    const newTbody = tbody.cloneNode(true);
    const newHtml = newTbody.innerHTML
        .replaceAll(/\[0\]/g, `[${detailIndex}]`)
        .replaceAll(/details\[0\]/g, `details[${detailIndex}]`)
        .replaceAll(
            /onchange="updateExpiredDate\(this, 0\)"/g,
            `onchange="updateExpiredDate(this, ${detailIndex})"`
        );


    // Buat ulang card baru
    const card = document.createElement('div');
    card.classList.add('card', 'shadow', 'mb-3', 'product-detail');
    card.innerHTML = `
            <div class="card-header d-flex justify-content-between">
                <strong>Detail Produk</strong>
                <button type="button" class="btn btn-danger btn-sm remove-detail">Hapus</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center align-middle table-sm">
                    ${original.querySelector('thead').outerHTML}
                    <tbody>${newHtml}</tbody>
                </table>
            </div>
        `;

    // Reset semua input (kecuali hidden sequence)
    card.querySelectorAll('input, select').forEach(input => {
        if (input.type !== 'hidden') input.value = '';
    });

    container.appendChild(card);
    detailIndex++;
});


// tombol hapus
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        e.target.closest('.product-detail').remove();
    }
});

function updateExpiredDate(select, index) {
    const option = select.options[select.selectedIndex];
    const shelfLife = parseInt(option.getAttribute('data-shelf-life'));
    const createdAt = option.getAttribute('data-created-at');

    if (!shelfLife || !createdAt) return;

    const baseDate = new Date(createdAt);
    baseDate.setMonth(baseDate.getMonth() + shelfLife);

    const yyyy = baseDate.getFullYear();
    const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
    const dd = String(baseDate.getDate()).padStart(2, '0');

    const formatted = `${yyyy}-${mm}-${dd}`;

    const expiredInput = document.querySelector(`input[name="details[${index}][expired_date]"]`);
    if (expiredInput) expiredInput.value = formatted;
}
</script>
@endsection