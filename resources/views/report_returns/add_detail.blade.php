@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail ke Report Tanggal {{ $report->date }}</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_returns.details.store', $report->uuid) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label>Pilih Bahan Baku</label>
                    <select name="rm_uuid" class="form-control raw-material-select" required
                        onchange="fillSupplier(this)">
                        <option value="">Pilih</option>
                        @foreach ($rawMaterials as $rm)
                        <option value="{{ $rm->uuid }}" data-supplier="{{ $rm->supplier ?? '' }}">
                            {{ $rm->material_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>Supplier</label>
                    <input name="supplier" class="form-control supplier-input" required>
                </div>
                <div class="mb-2">
                    <label>Kode Produksi</label>
                    <input name="production_code" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Jumlah</label>
                    <div class="input-group">
                        <input name="quantity" type="number" class="form-control" required placeholder="Jumlah">
                        <select name="unit" class="form-select form-control" required>
                            <option value="KG">KG</option>
                            <option value="PCS">PCS</option>
                            <option value="BAG">BAG</option>
                            <option value="KARUNG">KARUNG</option>
                            <option value="BOX">BOX</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label>Alasan Hold</label>
                    <input name="hold_reason" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Tindak Lanjut</label>
                    <input name="action" class="form-control">
                </div>
                <button class="btn btn-primary mt-3">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>

<script>
function fillSupplier(select) {
    const supplier = select.options[select.selectedIndex].getAttribute('data-supplier') || '';
    const supplierInput = document.querySelector('.supplier-input');
    if (supplierInput) {
        supplierInput.value = supplier;
    }
}
</script>
@endsection