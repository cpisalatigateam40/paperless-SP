@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Tambah Pemeriksaan Magnet Trap</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_magnet_traps.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" required>
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <hr>
                <h5>Detail Pemeriksaan</h5>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="align-middle">Jam</th>
                            <th class="align-middle">Sumber</th>
                            <th class="align-middle">Temuan</th>
                            <th class="align-middle">Keterangan</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="detail-body">
                        <tr>
                            <td><input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" required></td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center align-items-center gap-3" style="gap: 1rem;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="details[0][source]"
                                            value="QC" required>
                                        <label class="form-check-label">QC</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="details[0][source]"
                                            value="Produksi">
                                        <label class="form-check-label">Produksi</label>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <input type="file" name="details[0][finding]" class="form-control" accept="image/*">
                            </td>
                            <td><input type="text" name="details[0][note]" class="form-control"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3 mt-2" id="add-row">+ Tambah
                        Baris</button>
                </div>

                <div class="mt-4 d-flex" style="gap: .5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                    <a href="{{ route('report_magnet_traps.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let rowIdx = 1;
document.getElementById('add-row').addEventListener('click', () => {
    const body = document.getElementById('detail-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="time" name="details[${rowIdx}][time]" class="form-control" value="{{ \Carbon\Carbon::now()->format('H:i') }}" required></td>
        <td class="text-center align-middle">
            <div class="d-flex justify-content-center align-items-center gap-3" style="gap: 1rem;">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="details[${rowIdx}][source]" value="QC" required>
                    <label class="form-check-label">QC</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="details[${rowIdx}][source]" value="Produksi">
                    <label class="form-check-label">Produksi</label>
                </div>
            </div>
        </td>
        <td><input type="file" name="details[${rowIdx}][finding]" class="form-control" accept="image/*"></td>
        <td><input type="text" name="details[${rowIdx}][note]" class="form-control"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
        </td>
    `;

    body.appendChild(row);
    rowIdx++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
});
</script>
@endsection