@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Laporan Pemasakan Maurer</h4>
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
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $report->date }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" readonly>
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}"
                                {{ $report->section_uuid == $section->uuid ? 'selected' : '' }}>
                                {{ $section->section_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Produk --}}
        <div class="accordion" id="produkAccordion">
            @for($i=0; $i<5; $i++) @php $detail=$report->details[$i] ?? null;
                $steps = [
                ['name'=>'SHOWERING','fields'=>['time_minutes_1','time_minutes_2']],
                ['name'=>'WARMING','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                ['name'=>'DRYINGI','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                ['name'=>'DRYINGII','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                ['name'=>'DRYINGIII','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                ['name' => 'DRYINGIV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                'time_minutes_1', 'time_minutes_2']],
                ['name' => 'DRYINGV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                'time_minutes_1', 'time_minutes_2']],
                ['name'=>'SMOKING','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                ['name'=>'COOKINGI','fields'=>['room_temperature_1','room_temperature_2','product_temperature_1','product_temperature_2','time_minutes_1','time_minutes_2','rh_1','rh_2']],
                ['name'=>'COOKINGII','fields'=>['room_temperature_1','room_temperature_2','product_temperature_1','product_temperature_2','time_minutes_1','time_minutes_2','rh_1','rh_2']],
                ['name'=>'EVAKUASI','fields'=>['time_minutes_1','time_minutes_2']],
                ];
                @endphp
                <div class="accordion-item mb-2">
                    <h3 class="accordion-header">
                        @php $isOpen = $detail ? true : false; @endphp
                        <button
                            class="accordion-button {{ $isOpen ? '' : 'collapsed' }} bg-primary bg-gradient text-white fw-semibold"
                            type="button" data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}"
                            style="border: none; border-radius: .5rem; padding: .5rem; font-size: 1.5rem;">
                            <i class="bi bi-box-seam me-2"></i> Data Produk #{{ $i+1 }}
                        </button>

                    </h3>
                    <div id="produk{{ $i }}" class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}"
                        data-bs-parent="#produkAccordion">
                        <div class="accordion-body card shadow">

                            {{-- Info Produk --}}
                            <div class="row g-3 mb-3 card-body">
                                <div class="col-md-4">
                                    <label>Nama Produk</label>
                                    <select name="details[{{ $i }}][product_uuid]" class="form-control product-selector"
                                        data-index="{{ $i }}">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->uuid }}"
                                            {{ $detail && $detail->product_uuid == $product->uuid ? 'selected' : '' }}>
                                            {{ $product->product_name }} {{ $product->nett_weight }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Kode Produksi</label>
                                    <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                        value="{{ $detail->production_code ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label>Untuk Kemasan (gr)</label>
                                    <input type="number" name="details[{{ $i }}][packaging_weight]" class="form-control"
                                        value="{{ $detail->packaging_weight ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label>Jumlah Trolley</label>
                                    <input type="number" name="details[{{ $i }}][trolley_count]" class="form-control"
                                        value="{{ $detail->trolley_count ?? '' }}">
                                </div>
                            </div>

                            {{-- Process Steps --}}
                            <div class="card mb-3">
                                <div class="card-header">A. Rumah Asap (Smoke House)</div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered small">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Proses</th>
                                                    <th>Suhu Ruang Standard</th>
                                                    <th>Suhu Ruang Aktual</th>
                                                    <th>RH Standard</th>
                                                    <th>RH Aktual</th>
                                                    <th>Waktu (menit) Standard</th>
                                                    <th>Waktu (menit) Aktual</th>
                                                    <th>Suhu Produk Standard</th>
                                                    <th>Suhu Produk AKtual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($steps as $index=>$step)
                                                @php
                                                $stepData = $detail ? $detail->processSteps->firstWhere('step_name',
                                                $step['name']) : null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="text" readonly class="form-control form-control-sm"
                                                            name="details[{{ $i }}][process_steps][{{ $index }}][step_name]"
                                                            value="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                            data-index="{{ $i }}"
                                                            data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}">
                                                    </td>
                                                    @foreach(['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2','product_temperature_1','product_temperature_2']
                                                    as $field)
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
                                    // Hitung default duration di server side
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
                                    @for($t=0; $t<1; $t++) <select class="form-control mb-2"
                                        name="details[{{ $i }}][thermocouple_positions][{{ $t }}][position_info]">
                                        <option value="">-- Pilih --</option>
                                        <option value="OK"
                                            {{ ($positions[$t]->position_info ?? '') == 'OK' ? 'selected' : '' }}>OK
                                        </option>
                                        <option value="Tidak Oke"
                                            {{ ($positions[$t]->position_info ?? '') == 'Tidak Oke' ? 'selected' : '' }}>
                                            Tidak Oke</option>
                                        </select>
                                        @endfor
                                </div>
                            </div>


                            {{-- Sensorik --}}
                            <div class="card mb-3">
                                <div class="card-header">Pemeriksaan Sensorik</div>
                                <div class="card-body row g-3">
                                    @foreach(['Kematangan'=>'ripeness','Aroma'=>'aroma','Tekstur'=>'texture','Warna'=>'color',
                                    'Rasa'=>'taste']
                                    as $label=>$field)
                                    <div class="col">
                                        <label>{{ $label }}</label>
                                        <select name="details[{{ $i }}][sensory_check][{{ $field }}]"
                                            class="form-control">
                                            <option value="">-- Pilih --</option>
                                            <option value="1"
                                                {{ (optional($detail?->sensoryCheck)->$field == 1) ? 'selected' : '' }}>
                                                OK</option>
                                            <option value="0"
                                                {{ (optional($detail?->sensoryCheck)->$field == 0) ? 'selected' : '' }}>
                                                Tidak OK</option>
                                        </select>
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
                                        <option value="1"
                                            {{ $detail && $detail->can_be_twisted === 1 ? 'selected' : '' }}>Bisa
                                        </option>
                                        <option value="0"
                                            {{ $detail && $detail->can_be_twisted === 0 ? 'selected' : '' }}>Tidak Bisa
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- Showering & Cooling Down --}}
                            @php $scd = $detail ? $detail->showeringCoolingDown : null; @endphp
                            <div class="card mb-3">
                                <div class="card-header">B. Showering & Cooling Down</div>
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
                                                ['label'=>'Suhu Ruangan / ST (°C)','field'=>'room_temp'],
                                                ['label'=>'Suhu Produk / CT (°C)','field'=>'product_temp'],
                                                ['label'=>'Waktu (menit)','field'=>'time_minutes']
                                                ] as $item)
                                                <tr>
                                                    <td>{{ $item['label'] }}</td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][showering_cooling_down][{{ $item['field'] }}_1]"
                                                            value="{{ $scd ? $scd->{$item['field'].'_1'} : '' }}"></td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][showering_cooling_down][{{ $item['field'] }}_2]"
                                                            value="{{ $scd ? $scd->{$item['field'].'_2'} : '' }}"></td>
                                                    <td class="bg-light text-center text-muted small">–</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td>Suhu pusat produk setelah keluar (°C)</td>
                                                    @foreach(['product_temp_after_exit_1','product_temp_after_exit_2','product_temp_after_exit_3']
                                                    as $key => $field)
                                                    <td>
                                                        <input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][showering_cooling_down][{{ $field }}]"
                                                            value="{{ $scd ? $scd->$field : '' }}"
                                                            id="temp_exit_{{ $i }}_{{ $key+1 }}"
                                                            oninput="calculateAvgExitTemp({{ $i }})">
                                                    </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                                                    <td colspan="3">
                                                        <input type="number" step="any"
                                                            class="form-control form-control-sm"
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


                            {{-- Cooking Loss --}}
                            <div class="card mb-3">
                                <div class="card-header">C. Cooking Loss</div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered small align-middle">
                                            <thead class="text-center fw-semibold">
                                                <tr>
                                                    <th>Kode Batch</th>
                                                    <th>Berat Mentah</th>
                                                    <th>Berat Matang</th>
                                                    <th>Loss (kg)</th>
                                                    <th>Loss (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $loss = $detail->cookingLosses[0] ?? null; @endphp
                                                <tr>
                                                    <td><input type="text" class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooking_losses][0][batch_code]"
                                                            value="{{ $loss->batch_code ?? '' }}"></td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooking_losses][0][raw_weight]"
                                                            value="{{ $loss->raw_weight ?? '' }}"></td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooking_losses][0][cooked_weight]"
                                                            value="{{ $loss->cooked_weight ?? '' }}"></td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooking_losses][0][loss_kg]"
                                                            value="{{ $loss->loss_kg ?? '' }}"></td>
                                                    <td><input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooking_losses][0][loss_percent]"
                                                            value="{{ $loss->loss_percent ?? '' }}"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endfor
        </div>

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
            endTotal += 24 * 60; // lewat tengah malam
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

document.addEventListener('DOMContentLoaded', function() {
    const maurerStandards = @json($maurerStandardMap);

    console.log('✅ Maurer Standards loaded:', maurerStandards);

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-selector')) {
            const select = e.target;
            const productUuid = select.value;
            const index = select.dataset.index;

            console.log(`➡️ Product selected: ${productUuid}`);
            console.log(`➡️ Data index: ${index}`);

            const relatedInputs = document.querySelectorAll(`input[data-index="${index}"]`);
            console.log(`🧩 Related inputs found:`, relatedInputs.length);

            if (!maurerStandards[productUuid]) {
                console.warn(`🚫 No MaurerStandard found for product: ${productUuid}`);
                relatedInputs.forEach(input => {
                    if (!input.disabled) input.value = '';
                });
                return;
            }

            relatedInputs.forEach(input => {
                const step = input.dataset.step;
                const field = input.dataset.field;

                console.log(`🔍 Input: step="${step}", field="${field}"`);

                if (!step || !field) {
                    console.warn('⚠️ Missing step or field');
                    return;
                }

                const stepData = maurerStandards[productUuid][step];
                console.log(`📦 Step data for "${step}":`, stepData);

                if (stepData && stepData[field] !== undefined) {
                    input.value = stepData[field];
                    console.log(`✅ Set value for [${step}][${field}]: ${stepData[field]}`);
                } else {
                    input.value = '';
                    console.warn(`❓ Data not found for [${step}][${field}]`);
                }
            });
        }
    });
});
</script>
@endsection