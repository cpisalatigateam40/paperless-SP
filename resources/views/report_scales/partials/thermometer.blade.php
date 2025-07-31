<div class="table-responsive">
    <table class="table table-bordered align-middle" id="thermo-table">
        <thead class="text-center">
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Jenis & Kode Thermometer</th>
                <th colspan="2">Pukul 1
                    <input type="time" id="thermo_time1" value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                        class="form-control form-control-sm mt-1" {{ $isEdit ? 'disabled' : '' }}>
                </th>
                <th colspan="2">Pukul 2
                    <input type="time" id="thermo_time2" value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                        class="form-control form-control-sm mt-1" {{ !$isEdit ? 'disabled' : '' }}>
                </th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th>0°C</th>
                <th>100°C</th>
                <th>0°C</th>
                <th>100°C</th>
            </tr>
        </thead>
        <tbody id="thermo-body">
            <tr>
                <td class="text-center">1</td>
                <td>
                    <select name="thermo_data[0][thermometer_uuid]" class="form-select form-control">
                        <option value="">-- Pilih Thermometer --</option>
                        @foreach($thermometers as $t)
                        <option value="{{ $t->uuid }}">{{ $t->type }} - {{ $t->code }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="thermo_data[0][time_1]" class="thermo-time1" value="08:00"
                        {{ $isEdit ? 'disabled' : '' }}>
                    <input type="hidden" name="thermo_data[0][time_2]" class="thermo-time2" value="14:00"
                        {{ !$isEdit ? 'disabled' : '' }}>
                </td>
                <td><input type="number" step="0.01" name="thermo_data[0][p1_0]" class="form-control"
                        {{ $isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="thermo_data[0][p1_100]" class="form-control"
                        {{ $isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="thermo_data[0][p2_0]" class="form-control"
                        {{ !$isEdit ? 'disabled' : '' }}></td>
                <td><input type="number" step="0.01" name="thermo_data[0][p2_100]" class="form-control"
                        {{ !$isEdit ? 'disabled' : '' }}></td>
                <td>
                    <input type="text" name="thermo_data[0][status]" class="form-control">
                </td>

            </tr>
        </tbody>
    </table>

    <button type="button" class="btn btn-outline-primary btn-sm" id="add-thermo-row">
        + Tambah Detail Thermometer
    </button>
</div>

<script>
let thermoRow = 1;

document.getElementById('add-thermo-row').addEventListener('click', () => {
    const tbody = document.getElementById('thermo-body');
    const time1 = document.getElementById('thermo_time1').value;
    const time2 = document.getElementById('thermo_time2').value;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${thermoRow + 1}</td>
        <td>
            <select name="thermo_data[${thermoRow}][thermometer_uuid]" class="form-select form-control">
                <option value="">-- Pilih Thermometer --</option>
                @foreach($thermometers as $t)
                    <option value="{{ $t->uuid }}">{{ $t->type }} - {{ $t->code }}</option>
                @endforeach
            </select>
            <input type="hidden" name="thermo_data[${thermoRow}][time_1]" class="thermo-time1" value="${time1}" {{ $isEdit ? 'disabled' : '' }}>
            <input type="hidden" name="thermo_data[${thermoRow}][time_2]" class="thermo-time2" value="${time2}" {{ !$isEdit ? 'disabled' : '' }}>
        </td>
        <td><input type="number" step="0.01" name="thermo_data[${thermoRow}][p1_0]" class="form-control" {{ $isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="thermo_data[${thermoRow}][p1_100]" class="form-control" {{ $isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="thermo_data[${thermoRow}][p2_0]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}></td>
        <td><input type="number" step="0.01" name="thermo_data[${thermoRow}][p2_100]" class="form-control" {{ !$isEdit ? 'disabled' : '' }}></td>
        <td>
            <input type="text" name="thermo_data[${thermoRow}][status]" class="form-control">
        </td>

    `;

    tbody.appendChild(row);
    thermoRow++;
});

// Sync waktu ke seluruh baris thermometer
document.getElementById('thermo_time1').addEventListener('input', function() {
    document.querySelectorAll('.thermo-time1').forEach(input => input.value = this.value);
});

document.getElementById('thermo_time2').addEventListener('input', function() {
    document.querySelectorAll('.thermo-time2').forEach(input => input.value = this.value);
});
</script>