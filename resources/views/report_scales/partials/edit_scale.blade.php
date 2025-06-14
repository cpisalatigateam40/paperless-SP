<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Jenis & Kode Timbangan</th>
                <th colspan="3">
                    Pemeriksaan Pukul:
                    <input type="time" id="edit-time1" name="edit_time_1"
                        class="form-control form-control-sm mt-1"
                        value="{{ $details->first()?->time_1 ? date('H:i', strtotime($details->first()->time_1)) : '08:00' }}">
                </th>
                <th colspan="3">
                    Pemeriksaan Pukul:
                    <input type="time" id="edit-time2" name="edit_time_2"
                        class="form-control form-control-sm mt-1"
                        value="{{ $details->first()?->time_2 ? date('H:i', strtotime($details->first()->time_2)) : '14:00' }}">
                </th>
                <th>Keterangan</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
                @php
                    $m1 = $detail->measurements->where('inspection_time_index', 1)->keyBy('standard_weight');
                    $m2 = $detail->measurements->where('inspection_time_index', 2)->keyBy('standard_weight');
                @endphp

                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <select name="data[{{ $i }}][scale_uuid]" class="form-select form-control">
                            <option value="">-- Pilih Timbangan --</option>
                            @foreach($scales as $scale)
                                <option value="{{ $scale->uuid }}" {{ $detail->scale_uuid == $scale->uuid ? 'selected' : '' }}>
                                    {{ $scale->type }} - {{ $scale->code }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="data[{{ $i }}][time_1]" value="{{ $detail->time_1 ? date('H:i', strtotime($detail->time_1)) : '08:00' }}">
                        <input type="hidden" name="data[{{ $i }}][time_2]" value="{{ $detail->time_2 ? date('H:i', strtotime($detail->time_2)) : '14:00' }}">
                    </td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p1_1000]" class="form-control"  value="{{ optional($m1->get(1000))->measured_value }}"></td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p1_5000]" class="form-control" value="{{ optional($m1->get(5000))->measured_value }}"></td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p1_10000]" class="form-control" value="{{ optional($m1->get(10000))->measured_value }}"></td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p2_1000]" class="form-control" value="{{ optional($m2->get(1000))->measured_value }}"></td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p2_5000]" class="form-control" value="{{ optional($m2->get(5000))->measured_value }}"></td>
                    <td><input type="number" step="0.01" name="data[{{ $i }}][p2_10000]" class="form-control" value="{{ optional($m2->get(10000))->measured_value }}"></td>
                    <td>
                        <select name="data[{{ $i }}][status]" class="form-select form-control">
                            <option value="1" {{ $detail->notes == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="0" {{ $detail->notes != 'OK' ? 'selected' : '' }}>Tidak OK</option>
                        </select>
                    </td>
                </tr>
            @endforeach


        </tbody>
    </table>
</div>

<script>
    const updateAllTimes = () => {
        const t1 = document.getElementById('edit-time1').value;
        const t2 = document.getElementById('edit-time2').value;

        document.querySelectorAll('.time1-hidden').forEach(input => input.value = t1);
        document.querySelectorAll('.time2-hidden').forEach(input => input.value = t2);
    };

    document.getElementById('edit-time1').addEventListener('change', updateAllTimes);
    document.getElementById('edit-time2').addEventListener('change', updateAllTimes);

    // Jalankan awal untuk set semua waktu
    updateAllTimes();
</script>
