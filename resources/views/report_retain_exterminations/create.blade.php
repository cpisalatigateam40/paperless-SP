@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Report Pemusnahan Retain Sample</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_retain_exterminations.store') }}">
                @csrf
                {{-- Header --}}
                <div class="mb-3">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                        </div>
                    </div>
                </div>

                {{-- Detail --}}
                <div>
                    <div>
                        <h5 class="mt-5 mb-3">Detail Retain Sample</h5>
                        <table class="table table-bordered" id="detail-table">
                            <thead>
                                <tr>
                                    <th>Nama Retain</th>
                                    <th>Exp Date</th>
                                    <th>Kondisi</th>
                                    <th>Bentuk</th>
                                    <th>Jumlah</th>
                                    <th>Jumlah Kg</th>
                                    <th>Keterangan</th>
                                    <th>
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="addRow()">+</button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="details[0][retain_name]" class="form-control" required>
                                    </td>
                                    <td><input type="date" name="details[0][exp_date]" class="form-control" required>
                                    </td>
                                    <td><input type="text" name="details[0][retain_condition]" class="form-control"
                                            required>
                                    </td>
                                    <td>
                                        <select name="details[0][shape]" class="form-control" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Box">Box</option>
                                            <option value="Karung">Karung</option>
                                            <option value="Plastik">Plastik</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="details[0][quantity]" class="form-control" required>
                                    </td>
                                    <td><input type="number" step="0.01" name="details[0][quantity_kg]"
                                            class="form-control" required>
                                    </td>
                                    <td><input type="text" name="details[0][notes]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm"
                                            onclick="removeRow(this)">-</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-primary">Simpan Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let index = 1;

function addRow() {
    let html = `
            <tr>
                <td><input type="text" name="details[${index}][retain_name]" class="form-control" required></td>
                <td><input type="date" name="details[${index}][exp_date]" class="form-control" required></td>
                <td><input type="text" name="details[${index}][retain_condition]" class="form-control" required></td>
                <td>
                    <select name="details[${index}][shape]" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="Box">Box</option>
                        <option value="Karung">Karung</option>
                        <option value="Plastik">Plastik</option>
                    </select>
                </td>
                <td><input type="number" name="details[${index}][quantity]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="details[${index}][quantity_kg]" class="form-control" required></td>
                <td><input type="text" name="details[${index}][notes]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">-</button></td>
            </tr>`;
    $('#detail-table tbody').append(html);
    index++;
}

function removeRow(button) {
    $(button).closest('tr').remove();
}
</script>
@endsection