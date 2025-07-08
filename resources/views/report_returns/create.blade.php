@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Laporan Ketidaksesuaian Bahan Baku</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_returns.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label for="date">Tanggal</label>
                    <input type="date" name="date" id="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="mb-2">
                    <label for="shift">Shift</label>
                    <input name="shift" id="shift" class="form-control" value="{{ getShift() }}" required>
                </div>

                <hr>
                <h5 class="mb-4 mt-4">Detail Retur</h5>
                <div id="detail-container">
                    <div class="row mb-2 detail-item">
                        <div class="col">
                            <label>Bahan Baku</label>
                            <select name="details[0][rm_uuid]" class="form-control raw-material-select" required
                                onchange="fillSupplier(this)">
                                <option value="">Pilih Bahan Baku</option>
                                @foreach ($rawMaterials as $rm)
                                <option value="{{ $rm->uuid }}" data-supplier="{{ $rm->supplier ?? '' }}">
                                    {{ $rm->material_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>Supplier</label>
                            <input name="details[0][supplier]" class="form-control supplier-input" required>
                        </div>
                        <div class="col">
                            <label>Kode Produksi</label>
                            <input name="details[0][production_code]" class="form-control" required>
                        </div>
                        <div class="col">
                            <label>Jumlah</label>
                            <div class="input-group">
                                <input name="details[0][quantity]" type="number" class="form-control" required
                                    placeholder="Jumlah">
                                <select name="details[0][unit]" class="form-select form-control" required>
                                    <option value="KG">KG</option>
                                    <option value="PCS">PCS</option>
                                    <option value="BAG">BAG</option>
                                    <option value="KARUNG">KARUNG</option>
                                    <option value="BOX">BOX</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <label>Alasan Hold</label>
                            <input name="details[0][hold_reason]" class="form-control">
                        </div>
                        <div class="col">
                            <label>Tindak Lanjut</label>
                            <input name="details[0][action]" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mt-4" onclick="addDetail()">+ Tambah Baris
                    Detail</button>

                <button class="btn btn-primary mt-4">Simpan</button>
            </form>
        </div>
    </div>
</div>

<script>
let detailIndex = 1;

function addDetail() {
    let html = `
        <div class="row mb-2 detail-item">
            <div class="col">
                <label>Bahan Baku</label>
                <select name="details[${detailIndex}][rm_uuid]" class="form-control raw-material-select" required onchange="fillSupplier(this)">
                    <option value="">Pilih Bahan Baku</option>
                    @foreach ($rawMaterials as $rm)
                        <option value="{{ $rm->uuid }}" data-supplier="{{ $rm->supplier ?? '' }}">{{ $rm->material_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>Supplier</label>
                <input name="details[${detailIndex}][supplier]" class="form-control supplier-input" required>
            </div>
            <div class="col">
                <label>Kode Produksi</label>
                <input name="details[${detailIndex}][production_code]" class="form-control" required>
            </div>
            <div class="col">
        <label>Jumlah</label>
        <div class="input-group">
            <input name="details[${detailIndex}][quantity]" type="number" class="form-control" required placeholder="Jumlah">
                <select name="details[${detailIndex}][unit]" class="form-select form-control" required>
                    <option value="KG">KG</option>
                    <option value="PCS">PCS</option>
                    <option value="BAG">BAG</option>
                    <option value="KARUNG">KARUNG</option>
                    <option value="BOX">BOX</option>
                </select>
            </div>
        </div>

            <div class="col">
                <label>Alasan Hold</label>
                <input name="details[${detailIndex}][hold_reason]" class="form-control">
            </div>
            <div class="col">
                <label>Tindak Lanjut</label>
                <input name="details[${detailIndex}][action]" class="form-control">
            </div>
        </div>`;
    document.getElementById('detail-container').insertAdjacentHTML('beforeend', html);
    detailIndex++;
}

// Auto-fill supplier ketika pilih raw material
function fillSupplier(select) {
    const selectedOption = select.options[select.selectedIndex];
    const supplier = selectedOption.getAttribute('data-supplier') || '';
    // Cari input supplier di parent row
    const row = select.closest('.detail-item');
    const supplierInput = row.querySelector('.supplier-input');
    if (supplierInput) {
        supplierInput.value = supplier;
    }
}
</script>
@endsection