<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Jenis & Kode Thermometer</th>
                <th colspan="2">
                    Pemeriksaan Pukul:
                    <input type="time" id="edit-time1" class="form-control form-control-sm mt-1" value="{{ date('H:i', strtotime($details->first()?->time_1 ?? '08:00')) }}">
                </th>
                <th colspan="2">
                    Pemeriksaan Pukul:
                    <input type="time" id="edit-time2" class="form-control form-control-sm mt-1" value="{{ date('H:i', strtotime($details->first()?->time_2 ?? '14:00')) }}">
                </th>
                <th>Keterangan</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>0°C</th>
                <th>100°C</th>
                <th>0°C</th>
                <th>100°C</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
                @php
                    $m1 = $detail->measurements->where('inspection_time_index', 1)->keyBy('standard_temperature');
                    $m2 = $detail->measurements->where('inspection_time_index', 2)->keyBy('standard_temperature');
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <select name="thermo_data[{{ $i }}][thermometer_uuid]" class="form-select form-control">
                            <option value="">-- Pilih Thermometer --</option>
                            @foreach($thermometers as $t)
                                <option value="{{ $t->uuid }}" {{ $detail->thermometer_uuid == $t->uuid ? 'selected' : '' }}>
                                    {{ $t->type }} - {{ $t->code }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="thermo_data[{{ $i }}][time_1]" class="time1-hidden" value="{{ date('H:i', strtotime($detail->time_1)) }}">
                        <input type="hidden" name="thermo_data[{{ $i }}][time_2]" class="time2-hidden" value="{{ date('H:i', strtotime($detail->time_2)) }}">

                    </td>
                    <td><input type="number" step="0.01" name="thermo_data[{{ $i }}][p1_0]" class="form-control" value="{{ $m1[0]->measured_value ?? '' }}" {{ $isEdit ? 'readonly' : '' }}></td>
                    <td><input type="number" step="0.01" name="thermo_data[{{ $i }}][p1_100]" class="form-control" value="{{ $m1[100]->measured_value ?? '' }}" {{ $isEdit ? 'readonly' : '' }}></td>
                    <td><input type="number" step="0.01" name="thermo_data[{{ $i }}][p2_0]" class="form-control" value="{{ $m2[0]->measured_value ?? '' }}" {{ !$isEdit ? 'readonly' : '' }}></td>
                    <td><input type="number" step="0.01" name="thermo_data[{{ $i }}][p2_100]" class="form-control" value="{{ $m2[100]->measured_value ?? '' }}" {{ !$isEdit ? 'readonly' : '' }}></td>
                    <td>
                        <select name="thermo_data[{{ $i }}][status]" class="form-select form-control">
                            <option value="1" {{ $detail->note == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="0" {{ $detail->note != 'OK' ? 'selected' : '' }}>Tidak OK</option>
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // const updateThermoTimes = () => {
    //     const t1 = document.getElementById('edit-time1').value;
    //     const t2 = document.getElementById('edit-time2').value;

    //     document.querySelectorAll('.time1-hidden').forEach(el => el.value = t1);
    //     document.querySelectorAll('.time2-hidden').forEach(el => el.value = t2);
    // };

    // document.getElementById('edit-time1').addEventListener('change', updateThermoTimes);
    // document.getElementById('edit-time2').addEventListener('change', updateThermoTimes);
    // updateThermoTimes();
</script>
