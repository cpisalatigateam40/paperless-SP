@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Tambah Detail Pemeriksaan untuk Laporan: {{ $report->date }} (Shift {{ $report->shift }})</h4>

    <form action="{{ route('report_checkweigher_boxes.store-detail', $report->uuid) }}" method="POST">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-body">
                <div id="detail-wrapper">
                    <div class="detail-row border rounded p-3 mb-4">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">PEMERIKSAAN CHECKWEIGHER</span>
                            <small class="text-muted">Data 1</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Nama Produk</label>
                                <select name="details[0][product_uuid]" class="form-control"
                                    onchange="updateExpiredDate(this, 0)" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        data-shelf-life="{{ $product->shelf_life ?? 0 }}"
                                        data-created-at="{{ date('Y-m-d') }}">
                                        {{ $product->product_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Waktu Pengecekan</label>
                                <input type="time" name="details[0][time_inspection]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Tanggal Expired</label>
                                <input type="date" name="details[0][expired_date]" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">No. Program</label>
                                <input type="text" name="details[0][program_number]" class="form-control">
                            </div>
                        </div>

                        <hr class="mt-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="fw-semibold">Verifikasi Berat Checkweigher</span>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Checkweigher (gram)</label>
                                        <input type="number" step="0.01" name="details[0][checkweigher_weight_gr]"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Manual (gram)</label>
                                        <input type="number" step="0.01" name="details[0][manual_weight_gr]"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <span class="fw-semibold">Verifikasi Fungsi Rejector</span>
                                <div class="form-check mt-3">
                                    <input type="checkbox" name="details[0][double_item]" class="form-check-input"
                                        id="double_item_0">
                                    <label class="form-check-label" for="double_item_0">Double Item</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="details[0][weight_under]" class="form-check-input"
                                        id="weight_under_0">
                                    <label class="form-check-label" for="weight_under_0">Berat Kurang (Under)</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="details[0][weight_over]" class="form-check-input"
                                        id="weight_over_0">
                                    <label class="form-check-label" for="weight_over_0">Berat Lebih (Over)</label>
                                </div>
                            </div>
                        </div>

                        <div class="row ">
                            <div class="col-md-6 mb-2 mt-2">
                                <label class="form-label">Tindakan Perbaikan</label>
                                <input type="text" name="details[0][corrective_action]" class="form-control mt-2"
                                    placeholder="Isi jika ada perbaikan">
                            </div>
                            <div class="col-md-6 mb-2 mt-2">
                                <label class="form-label">Verifikasi Setelah Perbaikan</label>
                                <select name="details[0][verification]" class="form-select form-control mt-2">
                                    <option value="">-- Pilih --</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak OK">Tidak OK</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-secondary" onclick="addDetailRow()">
                    <i class="fas fa-plus"></i> Tambah Detail
                </button>
            </div>

            <div class="card-footer">
                <button class="btn btn-success" type="submit">Simpan Detail</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;

function updateExpiredDate(select, index) {
    const option = select.options[select.selectedIndex];
    const shelfLife = option.getAttribute('data-shelf-life');
    const createdAt = option.getAttribute('data-created-at');

    if (!shelfLife || !createdAt) return;

    const createdDate = new Date(createdAt);
    createdDate.setMonth(createdDate.getMonth() + parseInt(shelfLife));

    const yyyy = createdDate.getFullYear();
    const mm = String(createdDate.getMonth() + 1).padStart(2, '0');
    const dd = String(createdDate.getDate()).padStart(2, '0');

    const formattedDate = `${yyyy}-${mm}-${dd}`;
    const expiredInput = document.querySelector(`input[name="details[${index}][expired_date]"]`);
    if (expiredInput) expiredInput.value = formattedDate;
}

function addDetailRow() {
    const wrapper = document.getElementById('detail-wrapper');
    const html = wrapper.children[0].outerHTML
        .replaceAll('[0]', `[${detailIndex}]`)
        .replaceAll('_0', `_${detailIndex}`)
        .replaceAll('updateExpiredDate(this, 0)', `updateExpiredDate(this, ${detailIndex})`);

    wrapper.insertAdjacentHTML('beforeend', html);

    const lastRow = wrapper.lastElementChild;
    const newSelect = lastRow.querySelector(`select[name="details[${detailIndex}][product_uuid]"]`);

    if (newSelect) {
        newSelect.addEventListener('change', function() {
            updateExpiredDate(this, detailIndex);
        });
    }

    detailIndex++;
}
</script>
@endsection