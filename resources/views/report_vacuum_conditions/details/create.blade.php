@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Report Tanggal {{ $report->date }} (Shift: {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_vacuum_conditions.details.store', $report->uuid) }}">
                @csrf

                <table class="table table-bordered" id="detail-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jam</th>
                            <th>Kode Produksi</th>
                            <th>Expired Date</th>
                            <th>Jumlah Pack</th>
                            <th>Seal Bocor</th>
                            <th>Melipat Bocor</th>
                            <th>Casing Bocor</th>
                            <th>Lain-lain</th>
                            <th><button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="details[0][product_uuid]" class="form-control"
                                    onchange="updateExpiredDate(this, 0)" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}"
                                        data-shelf-life="{{ $product->shelf_life ?? 0 }}"
                                        data-created-at="{{ $product->created_at }}">
                                        {{ $product->product_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="time" name="details[0][time]" class="form-control" required></td>
                            <td><input type="text" name="details[0][production_code]" class="form-control" required>
                            </td>
                            <td><input type="date" name="details[0][expired_date]" class="form-control" readonly></td>
                            <td><input type="number" name="details[0][pack_quantity]" class="form-control" required>
                            </td>
                            <td><input type="checkbox" name="details[0][leaking_area_seal]" value="1"></td>
                            <td><input type="checkbox" name="details[0][leaking_area_melipat]" value="1"></td>
                            <td><input type="checkbox" name="details[0][leaking_area_casing]" value="1"></td>
                            <td><input type="text" name="details[0][leaking_area_other]" class="form-control"></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('report_vacuum_conditions.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
let rowCount = 1;

function addRow() {
    let newRow = `
    <tr>
        <td>
            <select name="details[${rowCount}][product_uuid]" class="form-control" onchange="updateExpiredDate(this, ${rowCount})" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $product)
                <option value="{{ $product->uuid }}"
                    data-shelf-life="{{ $product->shelf_life ?? 0 }}"
                    data-created-at="{{ $product->created_at }}">
                    {{ $product->product_name }}
                </option>
                @endforeach
            </select>
        </td>
        <td><input type="time" name="details[${rowCount}][time]" class="form-control" required></td>
        <td><input type="text" name="details[${rowCount}][production_code]" class="form-control" required></td>
        <td><input type="date" name="details[${rowCount}][expired_date]" class="form-control" readonly></td>
        <td><input type="number" name="details[${rowCount}][pack_quantity]" class="form-control" required></td>
        <td><input type="checkbox" name="details[${rowCount}][leaking_area_seal]" value="1"></td>
        <td><input type="checkbox" name="details[${rowCount}][leaking_area_melipat]" value="1"></td>
        <td><input type="checkbox" name="details[${rowCount}][leaking_area_casing]" value="1"></td>
        <td><input type="text" name="details[${rowCount}][leaking_area_other]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">x</button></td>
    </tr>`;
    $('#detail-table tbody').append(newRow);
    rowCount++;
}

function removeRow(button) {
    $(button).closest('tr').remove();
}

function updateExpiredDate(select, index) {
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
        document.querySelector(`input[name="details[${index}][expired_date]"]`).value = expiredDateStr;
    }
}
</script>
@endsection