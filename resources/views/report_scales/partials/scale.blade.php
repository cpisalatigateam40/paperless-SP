<div class="table-responsive mt-4">
    <table class="table table-bordered align-middle" id="detail-table">
        <thead class="text-center">
            <tr>
                <th rowspan="3" class="align-middle">No</th>
                <th rowspan="3" class="align-middle">Jenis dan Kode Timbangan</th>
                <th colspan="6" class="align-middle">Pemeriksaan</th>
                <th rowspan="3" class="align-middle">Keterangan</th>
            </tr>
            <tr>
                <th colspan="3">Pemeriksaan Pukul:
                    <input type="time" id="time1" value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                        class="form-control form-control-sm mt-1" {{ $isEdit ? 'disabled' : '' }}>
                </th>
                <th colspan="3">Pemeriksaan Pukul:
                    <input type="time" id="time2" value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                        class="form-control form-control-sm mt-1" {{ !$isEdit ? 'disabled' : '' }}>
                </th>
            </tr>
            <tr>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
            </tr>
        </thead>
        <tbody id="detail-body">
            <tr>
                <td class="text-center">1</td>
                <td>
                    <select name="data[0][scale_uuid]" class="form-select form-control">
                        <option value="">-- Pilih Timbangan --</option>
                        @foreach($scales as $scale)
                        <option value="{{ $scale->uuid }}">{{ $scale->type }} - {{ $scale->code }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="data[0][time_1]" class="time1-input" value="08:00"
                        {{ $isEdit ? 'disabled' : '' }}>
                    <input type="hidden" name="data[0][time_2]" class="time2-input" value="14:00"
                        {{ !$isEdit ? 'disabled' : '' }}>
                </td>

                {{-- Pemeriksaan Pukul 1 --}}
                <td><input type="number" step="0.01" name="data[0][p1_1000]" class="form-control"
                        {{ $isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="data[0][p1_5000]" class="form-control"
                        {{ $isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="data[0][p1_10000]" class="form-control"
                        {{ $isEdit ? 'disabled' : '' }}></td>

                {{-- Pemeriksaan Pukul 2 --}}
                <td><input type="number" step="0.01" name="data[0][p2_1000]" class="form-control"
                        {{ !$isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="data[0][p2_5000]" class="form-control"
                        {{ !$isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="data[0][p2_10000]" class="form-control"
                        {{ !$isEdit ? 'disabled' : '' }}></td>

                {{-- Status --}}
                <td>
                    <input type="text" name="data[0][status]" class="form-control">
                </td>

            </tr>
        </tbody>
    </table>

    <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="add-row">
        + Tambah Detail Timbangan
    </button>
</div>

<script>
let rowCount = 1;

document.getElementById('add-row').addEventListener('click', function() {
    const tbody = document.getElementById('detail-body');
    const time1 = document.getElementById('time1').value;
    const time2 = document.getElementById('time2').value;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${rowCount + 1}</td>
        <td>
            <select name="data[${rowCount}][scale_uuid]" class="form-select form-control">
                <option value="">-- Pilih Timbangan --</option>
                @foreach($scales as $scale)
                    <option value="{{ $scale->uuid }}">{{ $scale->type }} - {{ $scale->code }}</option>
                @endforeach
            </select>
            <input type="hidden" name="data[${rowCount}][time_1]" class="time1-input" value="${time1}" {{ $isEdit ? 'disabled' : '' }}>
            <input type="hidden" name="data[${rowCount}][time_2]" class="time2-input" value="${time2}">
        </td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_1000]" class="form-control" {{ $isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_5000]" class="form-control" {{ $isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p1_10000]" class="form-control" {{ $isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_1000]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_5000]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="data[${rowCount}][p2_10000]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}></td>
        <td>
            <input type="text" name="data[${rowCount}][status]" class="form-control">
        </td>

    `;

    tbody.appendChild(row);
    rowCount++;
});

// Sync waktu ke seluruh baris
document.getElementById('time1').addEventListener('input', function() {
    document.querySelectorAll('.time1-input').forEach(input => input.value = this.value);
});

document.getElementById('time2').addEventListener('input', function() {
    document.querySelectorAll('.time2-input').forEach(input => input.value = this.value);
});
</script>