@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Pemasakan Maurer</h4>
        </div>
    </div>

    @if (session('error'))
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ session('error') }}
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success">
        <strong>Success:</strong> {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('report_maurer_cookings.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="card mb-4">
            <div class="card-header">Data Utama</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Produk --}}
        @for($i = 0; $i < 1; $i++)
        @php
            $detail = $report->details[$i] ?? null;
            $steps = [
                ['name' => 'SHOWERING', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                ['name' => 'WARMING', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGI', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGII', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGIII', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGIV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'SMOKING', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2']],
                ['name' => 'COOKINGI', 'fields' => ['room_temperature_1', 'room_temperature_2', 'product_temperature_1', 'product_temperature_2', 'time_minutes_1', 'time_minutes_2', 'rh_1', 'rh_2']],
                ['name' => 'COOKINGII', 'fields' => ['room_temperature_1', 'room_temperature_2', 'product_temperature_1', 'product_temperature_2', 'time_minutes_1', 'time_minutes_2', 'rh_1', 'rh_2']],
                ['name' => 'EVAKUASI', 'fields' => ['time_minutes_1', 'time_minutes_2']],
            ];
        @endphp

        <div class="card shadow mb-4">
            <div class="card-header bg-primary bg-gradient text-white fw-semibold fs-5">
                <i class="bi bi-box-seam me-2"></i> Data Produk #{{ $i + 1 }}
            </div>
            <div class="card-body">

                {{-- Info Produk --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Nama Produk</label>
                        <select name="details[{{ $i }}][product_uuid]"
                            class="form-control product-selector select2-product" data-index="{{ $i }}">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ $detail && $detail->product_uuid == $product->uuid ? 'selected' : '' }}>
                                {{ $product->product_name }} - {{ $product->nett_weight }} g
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Kode Produksi</label>
                        <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                            value="{{ $detail->production_code ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Untuk Kemasan (gr)</label>
                        <input type="number" name="details[{{ $i }}][packaging_weight]" class="form-control"
                            value="{{ $detail->packaging_weight ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Jumlah Trolley</label>
                        <input type="number" name="details[{{ $i }}][trolley_count]" class="form-control"
                            value="{{ $detail->trolley_count ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Jumlah Stick</label>
                        <input type="number" name="details[{{ $i }}][stick_count]" class="form-control"
                            value="{{ $detail->stick_count ?? '' }}">
                    </div>
                </div>

                {{-- Process Steps --}}
                <div class="card mb-3">
                    <div class="card-header">A. Rumah Asap (Smoke House)</div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered small" style="table-layout:fixed; width:100%;">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width:150px">Nama Proses</th>
                                        <th style="width:120px">Suhu Ruang Standard</th>
                                        <th style="width:120px">Suhu Ruang Aktual</th>
                                        <th style="width:120px">RH Standard</th>
                                        <th style="width:120px">RH Aktual</th>
                                        <th style="width:120px">Waktu (menit) Standard</th>
                                        <th style="width:120px">Waktu (menit) Aktual</th>
                                        <th style="width:120px">Suhu Produk Standard</th>
                                        <th style="width:120px">Suhu Produk Aktual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($steps as $index => $step)
                                    @php
                                        $stepData = $detail ? $detail->processSteps->firstWhere('step_name', $step['name']) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="text" readonly class="form-control form-control-sm"
                                                name="details[{{ $i }}][process_steps][{{ $index }}][step_name]"
                                                value="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                data-index="{{ $i }}"
                                                data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}">
                                        </td>
                                        @foreach(['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2', 'time_minutes_1', 'time_minutes_2', 'product_temperature_1', 'product_temperature_2'] as $field)
                                        <td>
                                            @if(in_array($field, $step['fields']))
                                            <input type="number" step="any"
                                                class="form-control form-control-sm"
                                                name="details[{{ $i }}][process_steps][{{ $index }}][{{ $field }}]"
                                                value="{{ $stepData ? $stepData->$field : '' }}"
                                                data-index="{{ $i }}"
                                                data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                data-field="{{ $field }}">
                                            @else
                                            <input type="number" step="any"
                                                class="form-control form-control-sm" disabled>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Lama Proses --}}
                <div class="card mb-3">
                    <div class="card-header">Lama Proses Total</div>
                    <div class="card-body row g-3">
                        @php
                            $totalProcessTime = optional($report->details[$i]->totalProcessTime ?? null);
                            $startTime = $totalProcessTime->start_time ?? '';
                            $endTime = $totalProcessTime->end_time ?? '';
                            $duration = '';
                            if ($startTime && $endTime) {
                                $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
                                $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
                                if ($end->lessThan($start)) {
                                    $end->addDay();
                                }
                                $duration = $start->diffInMinutes($end) . ' menit';
                            }
                        @endphp

                        <div class="col-md-4">
                            <label>Jam Mulai</label>
                            <input type="time" class="form-control"
                                name="details[{{ $i }}][total_process_time][start_time]"
                                value="{{ \Carbon\Carbon::parse($startTime)->format('H:i') ?? '' }}"
                                onchange="calculateDuration({{ $i }})" id="start_time_{{ $i }}">
                        </div>
                        <div class="col-md-4">
                            <label>Jam Selesai</label>
                            <input type="time" class="form-control"
                                name="details[{{ $i }}][total_process_time][end_time]"
                                value="{{ \Carbon\Carbon::parse($endTime)->format('H:i') ?? '' }}"
                                onchange="calculateDuration({{ $i }})" id="end_time_{{ $i }}">
                        </div>
                        <div class="col-md-4">
                            <label>Total Lama (menit)</label>
                            <input type="text" class="form-control" readonly
                                name="details[{{ $i }}][total_process_time][duration_display]"
                                id="duration_{{ $i }}" value="{{ $duration }}">
                        </div>
                    </div>
                </div>

                {{-- Posisi Thermocouple --}}
                <div class="card mb-3">
                    <div class="card-header">Posisi Thermocouple</div>
                    <div class="card-body col-md-6">
                        @php $positions = $detail ? $detail->thermocouplePositions : collect(); @endphp
                        @for($t = 0; $t < 1; $t++)
                        <select class="form-control mb-2"
                            name="details[{{ $i }}][thermocouple_positions][{{ $t }}][position_info]">
                            <option value="">-- Pilih --</option>
                            <option value="OK"
                                {{ ($positions[$t]->position_info ?? '') == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="Tidak Oke"
                                {{ ($positions[$t]->position_info ?? '') == 'Tidak Oke' ? 'selected' : '' }}>Tidak Oke</option>
                        </select>
                        @endfor
                    </div>
                </div>

                {{-- Pemeriksaan Sensorik --}}
                <div class="card mb-3">
                    <div class="card-header">Pemeriksaan Sensorik</div>
                    <div class="card-body row g-3">

                        @foreach([
                            'Kematangan' => 'ripeness',
                            'Aroma'      => 'aroma',
                            'Tekstur'    => 'texture',
                            'Warna'      => 'color',
                            'Rasa'       => 'taste'
                        ] as $label => $field)

                        @php
                            $sens  = optional($detail?->sensoryCheck);
                            $value = $sens->$field;
                            $note  = $sens->{$field . '_note'};
                        @endphp

                        <div class="col-md-6">
                            <label>{{ $label }}</label>

                            <select name="details[{{ $i }}][sensory_check][{{ $field }}]"
                                class="form-control mb-2 sensory-select"
                                data-target="note-{{ $i }}-{{ $field }}">
                                <option value="">-- Pilih --</option>
                                <option value="1" {{ $value == 1 ? 'selected' : '' }}>OK</option>
                                <option value="0" {{ $value == 0 ? 'selected' : '' }}>Tidak OK</option>
                            </select>

                            <input type="text"
                                name="details[{{ $i }}][sensory_check][{{ $field }}_note]"
                                class="form-control note-field mb-3 {{ $value == 0 ? '' : 'd-none' }}"
                                id="note-{{ $i }}-{{ $field }}"
                                value="{{ old("details.$i.sensory_check.$field" . "_note", $note) }}"
                                placeholder="Masukkan keterangan...">
                        </div>

                        @endforeach

                    </div>
                </div>

                {{-- Bisa / Tidak Bisa Di Ulir --}}
                <div class="card mb-3">
                    <div class="card-header">Bisa / Tidak Bisa Di Ulir (khusus sosis ayam okey)</div>
                    <div class="card-body col-md-6">
                        <select name="details[{{ $i }}][can_be_twisted]" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ $detail && $detail->can_be_twisted === 1 ? 'selected' : '' }}>Bisa</option>
                            <option value="0" {{ $detail && $detail->can_be_twisted === 0 ? 'selected' : '' }}>Tidak Bisa</option>
                        </select>
                    </div>
                </div>

                {{-- Showering & Cooling Down --}}
                @php $scd = $detail ? $detail->showeringCoolingDown : null; @endphp
                <div class="card mb-3">
                    <div class="card-header">B. Showering &amp; Cooling Down</div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered small align-middle">
                                <thead class="text-center fw-semibold">
                                    <tr>
                                        <th>Nama Proses</th>
                                        <th>1</th>
                                        <th>2</th>
                                        <th>3</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold">SHOWERING</td>
                                        <td colspan="3">
                                            <input type="text" class="form-control form-control-sm"
                                                name="details[{{ $i }}][showering_cooling_down][showering_time]"
                                                value="{{ $scd->showering_time ?? '' }}">
                                        </td>
                                    </tr>
                                    <tr class="table-secondary text-center fw-semibold">
                                        <td colspan="4">COOLING DOWN</td>
                                    </tr>
                                    @foreach([
                                        ['label' => 'Suhu Ruangan / ST (°C)', 'field' => 'room_temp'],
                                        ['label' => 'Suhu Produk / CT (°C)',  'field' => 'product_temp'],
                                        ['label' => 'Waktu (menit)',          'field' => 'time_minutes']
                                    ] as $item)
                                    <tr>
                                        <td>{{ $item['label'] }}</td>
                                        <td><input type="number" step="any" class="form-control form-control-sm"
                                                name="details[{{ $i }}][showering_cooling_down][{{ $item['field'] }}_1]"
                                                value="{{ $scd ? $scd->{$item['field'] . '_1'} : '' }}"></td>
                                        <td><input type="number" step="any" class="form-control form-control-sm"
                                                name="details[{{ $i }}][showering_cooling_down][{{ $item['field'] }}_2]"
                                                value="{{ $scd ? $scd->{$item['field'] . '_2'} : '' }}"></td>
                                        <td class="bg-light text-center text-muted small">–</td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td>Suhu pusat produk setelah keluar (°C)</td>
                                        @foreach(['product_temp_after_exit_1', 'product_temp_after_exit_2', 'product_temp_after_exit_3'] as $key => $field)
                                        <td>
                                            <input type="number" step="any" class="form-control form-control-sm"
                                                name="details[{{ $i }}][showering_cooling_down][{{ $field }}]"
                                                value="{{ $scd ? $scd->$field : '' }}"
                                                id="temp_exit_{{ $i }}_{{ $key + 1 }}"
                                                oninput="calculateAvgExitTemp({{ $i }})">
                                        </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                                        <td colspan="3">
                                            <input type="number" step="any" class="form-control form-control-sm"
                                                name="details[{{ $i }}][showering_cooling_down][avg_product_temp_after_exit]"
                                                value="{{ $scd->avg_product_temp_after_exit ?? '' }}"
                                                id="avg_exit_temp_{{ $i }}" readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>{{-- end card-body --}}
        </div>{{-- end card --}}

        @endfor

        <button class="btn btn-success mt-3">Update Laporan</button>
    </form>
</div>
@endsection

@section('script')
<script>
function calculateDuration(i) {
    let start = document.getElementById('start_time_' + i).value;
    let end = document.getElementById('end_time_' + i).value;

    if (start && end) {
        let [startHour, startMin] = start.split(':').map(Number);
        let [endHour, endMin] = end.split(':').map(Number);

        let startTotal = startHour * 60 + startMin;
        let endTotal = endHour * 60 + endMin;

        if (endTotal < startTotal) {
            endTotal += 24 * 60;
        }

        let diff = endTotal - startTotal;
        document.getElementById('duration_' + i).value = diff + ' menit';
    } else {
        document.getElementById('duration_' + i).value = '';
    }
}

function calculateAvgExitTemp(i) {
    let temps = [];
    for (let t = 1; t <= 3; t++) {
        let val = parseFloat(document.getElementById('temp_exit_' + i + '_' + t).value);
        if (!isNaN(val)) {
            temps.push(val);
        }
    }
    let avg = 0;
    if (temps.length > 0) {
        avg = temps.reduce((a, b) => a + b, 0) / temps.length;
    }
    document.getElementById('avg_exit_temp_' + i).value = avg;
}
</script>

<script>
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('sensory-select')) {
        const targetId = e.target.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (e.target.value == '0') {
            input.classList.remove('d-none');
        } else {
            input.classList.add('d-none');
            input.value = '';
        }
    }
});
</script>
@endsection