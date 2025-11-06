@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report-premixes.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Edit Laporan Verifikasi Premix</h4>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <div>
                    <h5>Detail Premix Diperiksa</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="detail-table">
                            <thead>
                                <tr>
                                    <th>Nama Premix</th>
                                    <th>Kode Produksi</th>
                                    <th>Berat (gr)</th>
                                    <th>Digunakan untuk Batch</th>
                                    <th>Keterangan</th>
                                    <th>Tindakan Koreksi</th>
                                    <th>Verifikasi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $index => $detail)
                                <tr>
                                    <td>
                                        <select name="details[{{ $index }}][premix_uuid]"
                                            class="form-select form-control" data-row="{{ $index }}" required>
                                            <option value="">-- Pilih Premix --</option>
                                            @foreach ($premixes as $premix)
                                            <option value="{{ $premix->uuid }}"
                                                {{ $premix->uuid == $detail->premix_uuid ? 'selected' : '' }}>
                                                {{ $premix->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="details[{{ $index }}][production_code]"
                                            value="{{ $detail->production_code }}" class="form-control"></td>
                                    <td><input type="number" name="details[{{ $index }}][weight]"
                                            value="{{ $detail->weight }}" class="form-control" required></td>
                                    <td><input type="text" name="details[{{ $index }}][used_for_batch]"
                                            value="{{ $detail->used_for_batch }}" class="form-control"></td>
                                    <td><input type="text" name="details[{{ $index }}][notes]"
                                            value="{{ $detail->notes }}" class="form-control"></td>
                                    <td><input type="text" name="details[{{ $index }}][corrective_action]"
                                            value="{{ $detail->corrective_action }}" class="form-control"></td>
                                    <td>
                                        <select name="details[{{ $index }}][verification]"
                                            class="form-select form-control">
                                            <option value="">--</option>
                                            <option value="✓" {{ $detail->verification == '✓' ? 'selected' : '' }}>✓
                                            </option>
                                            <option value="x" {{ $detail->verification == 'x' ? 'selected' : '' }}>x
                                            </option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- <div class="d-flex mt-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+ Tambah Baris</button>
                    </div> -->

                    <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                    <a href="{{ route('report-premixes.index') }}" class="btn btn-secondary mt-3">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection


@section('script')
<script>
let rowIdx = {
    {
        count($details)
    }
};
const premixes = @json($premixes);

function generatePremixOptions(selected = '') {
    return premixes.map(p => {
        return `<option value="${p.uuid}" ${selected === p.uuid ? 'selected' : ''}>${p.name}</option>`;
    }).join('');
}

function addRow() {
    const tbody = document.querySelector('#detail-table tbody');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <select name="details[${rowIdx}][premix_uuid]" class="form-select form-control" data-row="${rowIdx}" required>
                <option value="">-- Pilih Premix --</option>
                ${generatePremixOptions()}
            </select>
        </td>
        <td><input type="text" name="details[${rowIdx}][production_code]" class="form-control"></td>
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