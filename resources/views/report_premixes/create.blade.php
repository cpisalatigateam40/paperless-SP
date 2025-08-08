@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-premixes.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between">
                <h4>Buat Laporan Pemeriksaan Premix</h4>
            </div>
            <div class="card-body">
                <div class="row" style="margin-bottom: 2rem;">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <div>
                    <div>
                        <h5>Detail Premix Diperiksa</h5>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="detail-table">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Nama Premix</th>
                                        <th class="align-middle">Kode Produksi</th>
                                        <th class="align-middle">Berat (gr)</th>
                                        <th class="align-middle">Digunakan untuk Batch</th>
                                        <th class="align-middle">Keterangan</th>
                                        <th class="align-middle">Tindakan Koreksi</th>
                                        <th class="align-middle">Verifikasi</th>
                                        <th class="align-middle text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="details[0][premix_uuid]"
                                                class="form-select premix-select form-control" data-row="0" required>
                                                <option value="">-- Pilih Premix --</option>
                                                @foreach ($premixes as $premix)
                                                <option value="{{ $premix->uuid }}">
                                                    {{ $premix->name }}
                                                </option>
                                                @endforeach
                                            </select>

                                        </td>
                                        <td>
                                            <input type="text" name="details[0][production_code]" class="form-control">
                                        </td>

                                        <td><input type="number" name="details[0][weight]" class="form-control"
                                                required></td>
                                        <td><input type="text" name="details[0][used_for_batch]" class="form-control">
                                        </td>
                                        <td><input type="text" name="details[0][notes]" class="form-control"></td>
                                        <td><input type="text" name="details[0][corrective_action]"
                                                class="form-control"></td>
                                        <td>
                                            <select name="details[0][verification]" class="form-select form-control">
                                                <option value="">--</option>
                                                <option value="✓">✓</option>
                                                <option value="x">x</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="removeRow(this)">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex mt-3">
                            <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+ Tambah
                                Baris</button>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Simpan Laporan</button>
                        <a href="{{ route('report-premixes.index') }}" class="btn btn-secondary mt-3">Batal</a>
                    </div>
                </div>
            </div>
        </div>


    </form>
</div>
@endsection

@section('script')
<script>
let rowIdx = 1;

const premixes = @json($premixes);

function generatePremixOptions(row) {
    return premixes.map(p => {
        return `<option value="${p.uuid}">${p.name}</option>`;
    }).join('');
}

function addRow() {
    const tbody = document.querySelector('#detail-table tbody');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <select name="details[${rowIdx}][premix_uuid]" class="form-select premix-select form-control" data-row="${rowIdx}" required>
                <option value="">-- Pilih Premix --</option>
                ${generatePremixOptions(rowIdx)}
            </select>
        </td>
        <td>
            <input type="text" name="details[${rowIdx}][production_code]" class="form-control">
        </td>
        <td><input type="number" name="details[${rowIdx}][weight]" class="form-control" required></td>
        <td><input type="text" name="details[${rowIdx}][used_for_batch]" class="form-control"></td>
        <td><input type="text" name="details[${rowIdx}][notes]" class="form-control"></td>
        <td><input type="text" name="details[${rowIdx}][corrective_action]" class="form-control"></td>
        <td>
            <select name="details[${rowIdx}][verification]" class="form-select form-control">
                <option value="">--</option>
                <option value="✓">✓</option>
                <option value="x">x</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">Hapus</button>
        </td>
    `;
    tbody.appendChild(row);
    rowIdx++;
}

function removeRow(button) {
    button.closest('tr').remove();
}
</script>
@endsection