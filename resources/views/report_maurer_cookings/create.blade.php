@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Buat Laporan Pemasakan Maurer</h4>
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

    <form action="{{ route('report_maurer_cookings.store') }}" method="POST">
        @csrf

        {{-- Header --}}
        <div class="card mb-4">
            <div class="card-header">Data Utama</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control">
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Produk --}}
        <div class="accordion" id="produkAccordion">
            @for($i = 0; $i < 5; $i++) <div class="accordion-item mb-2">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed bg-primary bg-gradient text-white fw-semibold"
                        type="button" data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}"
                        style="border: none; border-radius: .5rem; padding: .5rem;">
                        <i class="bi bi-box-seam me-2"></i> Data Produk #{{ $i + 1 }}
                    </button>
                </h3>
                <div id="produk{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#produkAccordion">
                    <div class="accordion-body card shadow">

                        {{-- Info Produk --}}
                        <div class="row g-3 mb-3 card-body">
                            <div class="col-md-4">
                                <label>Nama Produk</label>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control product-selector"
                                    data-index="{{ $i }}">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}
                                        {{ $product->nett_weight }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[{{ $i }}][production_code]" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Untuk Kemasan (gr)</label>
                                <input type="number" name="details[{{ $i }}][packaging_weight]" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Jumlah Trolley</label>
                                <input type="number" name="details[{{ $i }}][trolley_count]" class="form-control">
                            </div>
                        </div>

                        {{-- Process Steps --}}
                        @php
                        $steps = [
                        ['name' => 'SHOWERING', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                        ['name' => 'WARMING', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DRYINGI', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DRYINGII', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DRYINGIII', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DRYINGIV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DRYINGV', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'SMOKING', 'fields' => ['room_temperature_1', 'room_temperature_2', 'rh_1', 'rh_2',
                        'time_minutes_1', 'time_minutes_2']],
                        ['name' => 'COOKINGI', 'fields' => ['room_temperature_1', 'room_temperature_2',
                        'product_temperature_1', 'product_temperature_2', 'time_minutes_1', 'time_minutes_2', 'rh_1',
                        'rh_2']],
                        ['name' => 'COOKINGII', 'fields' => ['room_temperature_1', 'room_temperature_2',
                        'product_temperature_1', 'product_temperature_2', 'time_minutes_1', 'time_minutes_2', 'rh_1',
                        'rh_2']],
                        ['name' => 'EVAKUASI', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                        ];
                        @endphp

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
                                            @foreach($steps as $index => $step)
                                            <tr>
                                                <td>
                                                    <input type="text" readonly class="form-control form-control-sm"
                                                        name="details[{{ $i }}][process_steps][{{ $index }}][step_name]"
                                                        value="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                        data-index="{{ $i }}"
                                                        data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}">
                                                </td>
                                                @foreach([
                                                'room_temperature_1',
                                                'room_temperature_2',
                                                'rh_1',
                                                'rh_2',
                                                'time_minutes_1',
                                                'time_minutes_2',
                                                'product_temperature_1',
                                                'product_temperature_2'
                                                ] as $field)
                                                <td>
                                                    @if(in_array($field, $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][process_steps][{{ $index }}][{{ $field }}]"
                                                        data-index="{{ $i }}"
                                                        data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                        data-field="{{ $field }}">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
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

                        <!-- <pre>{{ json_encode($maurerStandardMap, JSON_PRETTY_PRINT) }}</pre> -->

                        {{-- Row tambahan LAMA PROSES --}}
                        <div class="card mb-3">
                            <div class="card-header">Lama Proses Total</div>
                            <div class="card-body row g-3">
                                <div class="col-md-4">
                                    <label>Jam Mulai</label>
                                    <input type="time" class="form-control"
                                        name="details[{{ $i }}][total_process_time][start_time]"
                                        onchange="calculateDuration({{ $i }})" id="start_time_{{ $i }}"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>Jam Selesai</label>
                                    <input type="time" class="form-control"
                                        name="details[{{ $i }}][total_process_time][end_time]"
                                        onchange="calculateDuration({{ $i }})" id="end_time_{{ $i }}"
                                        value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>Total Lama (menit)</label>
                                    <input type="text" class="form-control" readonly
                                        name="details[{{ $i }}][total_process_time][duration_display]"
                                        id="duration_{{ $i }}">
                                </div>
                            </div>
                        </div>


                        {{-- Thermocouple --}}
                        <div class="card mb-3">
                            <div class="card-header">Posisi Thermocouple</div>
                            <div class="card-body col-md-6">
                                @for($t = 0; $t < 1; $t++) <select class="form-control mb-2"
                                    name="details[{{ $i }}][thermocouple_positions][{{ $t }}][position_info]">
                                    <option value="">-- Pilih --</option>
                                    <option value="OK">OK</option>
                                    <option value="Tidak Oke">Tidak Oke</option>
                                    </select>
                                    @endfor
                            </div>
                        </div>


                        {{-- Sensory --}}
                        <div class="card mb-3">
                            <div class="card-header">Pemeriksaan Sensorik</div>
                            <div class="card-body row g-3">
                                @foreach([
                                'Kematangan' => 'ripeness',
                                'Aroma' => 'aroma',
                                'Tekstur' => 'texture',
                                'Warna' => 'color',
                                'Rasa' => 'taste'
                                ]
                                as $label => $field)
                                <div class="col">
                                    <label>{{ $label }}</label>
                                    <select name="details[{{ $i }}][sensory_check][{{ $field }}]" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">Bisa / Tidak Bisa Di Ulir (khusus sosis ayam okey)</div>
                            <div class="card-body col-md-6">
                                <select name="details[{{ $i }}][can_be_twisted]" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <option value="1">Bisa</option>
                                    <option value="0">Tidak Bisa</option>
                                </select>
                            </div>
                        </div>

                        {{-- Showering & Cooling Down --}}
                        <div class="card mb-3">
                            <div class="card-header">B. Showering & Cooling Down</div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered small align-middle">
                                        <thead class="text-center fw-semibold">
                                            <tr>
                                                <th style="width: 30%;">Nama Proses</th>
                                                <th style="width: 20%;">1</th>
                                                <th style="width: 20%;">2</th>
                                                <th style="width: 20%;">3</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- SHOWERING --}}
                                            <tr>
                                                <td class="fw-semibold">SHOWERING</td>
                                                <td colspan="3">
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][showering_time]"
                                                        placeholder="">
                                                </td>
                                            </tr>

                                            {{-- COOLING DOWN header --}}
                                            <tr class="table-secondary text-center fw-semibold">
                                                <td colspan="4">COOLING DOWN</td>
                                            </tr>

                                            {{-- Suhu Ruangan / ST --}}
                                            <tr>
                                                <td>Suhu Ruangan / ST (°C)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][room_temp_1]"
                                                        placeholder="Suhu ruangan standard">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][room_temp_2]"
                                                        placeholder="Suhu ruangan aktual">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Suhu Produk / CT --}}
                                            <tr>
                                                <td>Suhu Produk / CT (°C)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_1]"
                                                        placeholder="Suhu produk standard">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_2]"
                                                        placeholder="Suhu produk aktual">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Waktu --}}
                                            <tr>
                                                <td>Waktu (menit)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][time_minutes_1]"
                                                        placeholder="Waktu menit 1">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][time_minutes_2]"
                                                        placeholder="Waktu menit 2">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Suhu pusat produk setelah keluar --}}
                                            <tr>
                                                <td>Suhu pusat produk setelah keluar (°C)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_1]"
                                                        id="temp1_{{ $i }}" oninput="calculateAverage({{ $i }})"
                                                        placeholder="Suhu pusat produk 1">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_2]"
                                                        id="temp2_{{ $i }}" oninput="calculateAverage({{ $i }})"
                                                        placeholder="Suhu pusat produk 2">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_3]"
                                                        id="temp3_{{ $i }}" oninput="calculateAverage({{ $i }})"
                                                        placeholder="Suhu pusat produk 3">
                                                </td>
                                            </tr>

                                            {{-- Suhu rata-rata pusat produk setelah keluar --}}
                                            <tr>
                                                <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                                                <td colspan="3">
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][avg_product_temp_after_exit]"
                                                        id="avg_temp_{{ $i }}" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Cooking Losses --}}
                        <!-- <div class="card mb-3">
                            <div class="card-header">C. Cooking Loss</div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered small align-middle">
                                        <thead class="text-center fw-semibold">
                                            <tr>
                                                <th style="width: 20%;">Kode Batch</th>
                                                <th style="width: 20%;">Berat Mentah (kg)</th>
                                                <th style="width: 20%;">Berat Matang (kg)</th>
                                                <th style="width: 20%;">Loss (kg)</th>
                                                <th style="width
                                               :     20%;">Loss (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($l = 0; $l < 1; $l++) <tr>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm text-center"
                                                        name="details[{{ $i }}][cooking_losses][{{ $l }}][batch_code]"
                                                        placeholder="">
                                                </td>
                                                <td>
                                                    <input type="number" step="any"
                                                        class="form-control form-control-sm text-end"
                                                        name="details[{{ $i }}][cooking_losses][{{ $l }}][raw_weight]"
                                                        placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" step="any"
                                                        class="form-control form-control-sm text-end"
                                                        name="details[{{ $i }}][cooking_losses][{{ $l }}][cooked_weight]"
                                                        placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" step="any"
                                                        class="form-control form-control-sm text-end"
                                                        name="details[{{ $i }}][cooking_losses][{{ $l }}][loss_kg]"
                                                        placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" step="any"
                                                        class="form-control form-control-sm text-end"
                                                        name="details[{{ $i }}][cooking_losses][{{ $l }}][loss_percent]"
                                                        placeholder="0">
                                                </td>
                                                </tr>
                                                @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> -->

                    </div>
                </div>
        </div>
        @endfor
</div>

<button class="btn btn-success mt-3">Simpan Laporan</button>
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

        // Jika end < start, berarti lewat tengah malam
        if (endTotal < startTotal) {
            endTotal += 24 * 60;
        }

        let diff = endTotal - startTotal;
        document.getElementById('duration_' + i).value = diff + ' menit';
    } else {
        document.getElementById('duration_' + i).value = '';
    }
}

function calculateAverage(i) {
    let t1 = parseFloat(document.getElementById('temp1_' + i).value) || 0;
    let t2 = parseFloat(document.getElementById('temp2_' + i).value) || 0;
    let t3 = parseFloat(document.getElementById('temp3_' + i).value) || 0;

    // Hitung jumlah input yang valid (>0) supaya rata-rata hanya dihitung dari input yang diisi
    let count = 0;
    if (document.getElementById('temp1_' + i).value !== '') count++;
    if (document.getElementById('temp2_' + i).value !== '') count++;
    if (document.getElementById('temp3_' + i).value !== '') count++;

    let avg = 0;
    if (count > 0) {
        avg = (t1 + t2 + t3) / count;
    }

    document.getElementById('avg_temp_' + i).value = avg;
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