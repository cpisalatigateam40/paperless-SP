@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Tambah Detail Produk ke Laporan Loss Vacuum</h4>

    <form action="{{ route('report_prod_loss_vacums.store-detail', $report->uuid) }}" method="POST">
        @csrf

        <div id="detail-container">
            <div class="card shadow mb-3 detail-item">
                <div class="card-body">
                    <h5 class="mb-5">Detail Produk</h5>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label>Nama Produk</label>
                            <select name="details[0][product_uuid]" class="form-control" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $product)
                                <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[0][production_code]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Mesin Vacuum</label>
                            <select name="details[0][vacum_machine]" class="form-control" required>
                                <option value="">-- Pilih Mesin --</option>
                                <option value="Manual">Manual</option>
                                <option value="Colimatic">Colimatic</option>
                                <option value="CFS">CFS</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Jumlah Sampel</label>
                            <input type="number" name="details[0][sample_amount]" class="form-control" required>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-4 mt-4">Hasil Pemeriksaan</h6>
                    <div class="row g-2">
                        @php
                        $defects = [
                        'Produk bagus',
                        'Seal tidak sempurna',
                        'Melipat',
                        'Casing terjepit',
                        'Top bergeser',
                        'Seal terlalu panas',
                        'Seal kurang panas',
                        'Sobek',
                        'Isi per pack tidak sesuai',
                        'Penataan produk tidak rapi',
                        'Produk tidak utuh',
                        'Lain-lain'
                        ];
                        @endphp
                        @foreach ($defects as $i => $def)
                        <div class="col-md-4">
                            <label>{{ $def }}</label>
                            <div class="input-group mb-4">
                                <input type="hidden" name="details[0][defects][{{ $i }}][category]" value="{{ $def }}">
                                <input type="number" name="details[0][defects][{{ $i }}][pack_amount]"
                                    class="form-control" placeholder="Jumlah Pack">
                                <input type="number" name="details[0][defects][{{ $i }}][percentage]" step="0.01"
                                    class="form-control" placeholder="%">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-3">
            <button type="button" class="btn btn-primary" id="add-detail">+ Tambah Produk</button>
        </div>

        <button type="submit" class="btn btn-success">Simpan Detail</button>
    </form>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;

const defects = [
    'Produk bagus', 'Seal tidak sempurna', 'Melipat', 'Casing terjepit',
    'Top bergeser', 'Seal terlalu panas', 'Seal kurang panas', 'Sobek',
    'Isi per pack tidak sesuai', 'Penataan produk tidak rapi', 'Produk tidak utuh', 'Lain-lain'
];

document.getElementById('add-detail').addEventListener('click', function() {
    const container = document.getElementById('detail-container');
    const item = document.createElement('div');
    item.classList.add('card', 'shadow', 'mb-3', 'detail-item');

    let defectInputs = '';
    defects.forEach((def, i) => {
        defectInputs += `
            <div class="col-md-4">
                <label>${def}</label>
                <div class="input-group mb-4">
                    <input type="hidden" name="details[${detailIndex}][defects][${i}][category]" value="${def}">
                    <input type="number" name="details[${detailIndex}][defects][${i}][pack_amount]" class="form-control" placeholder="Jumlah Pack">
                    <input type="number" name="details[${detailIndex}][defects][${i}][percentage]" step="0.01" class="form-control" placeholder="%">
                </div>
            </div>`;
    });

    item.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Detail Produk #${detailIndex + 1}</h5>
                <button type="button" class="btn btn-sm btn-danger remove-detail">Hapus</button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label>Nama Produk</label>
                    <select name="details[${detailIndex}][product_uuid]" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="details[${detailIndex}][production_code]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Mesin Vacuum</label>
                    <select name="details[${detailIndex}][vacum_machine]" class="form-control" required>
                        <option value="">-- Pilih Mesin --</option>
                        <option value="Manual">Manual</option>
                        <option value="Colimatic">Colimatic</option>
                        <option value="CFS">CFS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Jumlah Sampel</label>
                    <input type="number" name="details[${detailIndex}][sample_amount]" class="form-control" required>
                </div>
            </div>
            <hr>
            <h6 class="mb-4 mt-4">Hasil Pemeriksaan</h6>
            <div class="row g-2">
                ${defectInputs}
            </div>
        </div>`;

    container.appendChild(item);
    detailIndex++;
});

document.getElementById('detail-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-detail')) {
        e.target.closest('.detail-item').remove();
    }
});
</script>
@endsection