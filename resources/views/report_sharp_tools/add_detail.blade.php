    @extends('layouts.app')

    @section('content')
    <div class="container-fluid">
        <form action="{{ route('report_sharp_tools.details.store', $report->uuid) }}" method="POST">
            @csrf
            <div class="card shadow mb-3">
                <div class="card-header d-flex justify-content-between">
                    <h4>Tambah Baris Pemeriksaan</h4>
                    <a href="{{ route('report_sharp_tools.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label><strong>Tanggal</strong></label>
                            <input type="text" class="form-control" value="{{ $report->date }}" readonly>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label><strong>Shift</strong></label>
                            <input type="text" class="form-control" value="{{ $report->shift }}" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><strong>Area</strong></label>
                            <input type="text" class="form-control" value="{{ $report->area->name ?? '-' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header">
                    <h5>Detail Pemeriksaan Baru</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle text-center">#</th>
                                <th class="align-middle">Benda Tajam</th>
                                <th class="align-middle text-center">Jumlah Awal</th>
                                <th class="align-middle text-center">Jumlah Akhir</th>
                                <th class="align-middle text-center">Waktu 1</th>
                                <th class="align-middle text-center">Kondisi 1</th>
                                <th class="align-middle text-center">Waktu 2</th>
                                <th class="align-middle text-center">Kondisi 2</th>
                                <th class="align-middle text-center">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="detail-rows">
                            <tr>
                                <td class="align-middle text-center">1</td>
                                <td>
                                    <select name="details[0][sharp_tool_uuid]" class="form-control form-control-sm"
                                        required>
                                        <option value="">-- Pilih Alat --</option>
                                        @foreach($sharpTools as $tool)
                                        <option value="{{ $tool->uuid }}">{{ $tool->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="details[0][qty_start]"
                                        class="form-control form-control-sm">
                                </td>
                                <td><input type="number" name="details[0][qty_end]" class="form-control form-control-sm"
                                        disabled></td>
                                <td><input type="time" name="details[0][check_time_1]"
                                        class="form-control form-control-sm" value="{{ now()->format('H:i') }}"></td>
                                <td>
                                    <select name="details[0][condition_1]" class="form-control form-control-sm">
                                        <option value="">-</option>
                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="hilang">Hilang</option>
                                        <option value="tidaktersedia">Tidak Tersedia</option>
                                    </select>
                                </td>
                                <td><input type="time" name="details[0][check_time_2]"
                                        class="form-control form-control-sm" disabled></td>
                                <td>
                                    <select name="details[0][condition_2]" class="form-control form-control-sm"
                                        disabled>
                                        <option value="">-</option>
                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="hilang">Hilang</option>
                                        <option value="tidaktersedia">Tidak Tersedia</option>
                                    </select>
                                </td>
                                <td><input type="text" name="details[0][note]" class="form-control form-control-sm">
                                </td>

                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-row">Tambah
                        Baris</button>

                    <div class="mt-3">
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endsection

    @section('script')
    <script>
let rowIndex = 1;
const sharpToolOptions =
    `@foreach($sharpTools as $tool)<option value="{{ $tool->uuid }}">{{ $tool->name }}</option>@endforeach`;

document.getElementById('add-row').addEventListener('click', function() {
    const tableBody = document.getElementById('detail-rows');

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
            <td class="align-middle text-center">${rowIndex + 1}</td>
            <td>
                <select name="details[${rowIndex}][sharp_tool_uuid]" class="form-control form-control-sm" required>
                    <option value="">-- Pilih Alat --</option>
                    ${sharpToolOptions}
                </select>
            </td>
            <td><input type="number" name="details[${rowIndex}][qty_start]" class="form-control form-control-sm"></td>
            <td><input type="number" name="details[${rowIndex}][qty_end]" class="form-control form-control-sm" disabled></td>
            <td><input type="time" name="details[${rowIndex}][check_time_1]" class="form-control form-control-sm" value="{{ now()->format('H:i') }}"></td>
            <td>
                <select name="details[${rowIndex}][condition_1]" class="form-control form-control-sm">
                    <option value="">-</option>
                    <option value="baik">Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="hilang">Hilang</option>
                    <option value="tidaktersedia">Tidak Tersedia</option>
                </select>
            </td>
            <td><input type="time" name="details[${rowIndex}][check_time_2]" class="form-control form-control-sm" disabled></td>
            <td>
                <select name="details[${rowIndex}][condition_2]" class="form-control form-control-sm" disabled>
                    <option value="">-</option>
                    <option value="baik">Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="hilang">Hilang</option>
                    <option value="tidaktersedia">Tidak Tersedia</option>
                </select>
            </td>
            <td><input type="text" name="details[${rowIndex}][note]" class="form-control form-control-sm"></td>

        `;
    tableBody.appendChild(newRow);
    rowIndex++;
});
    </script>
    @endsection