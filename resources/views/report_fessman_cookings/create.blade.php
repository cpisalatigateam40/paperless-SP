@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Laporan Verifikasi Pemasakan Fessman</h4>
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

    <form action="{{ route('report_fessman_cookings.store') }}" method="POST">
        @csrf

        {{-- Data Utama --}}
        <div class="card mb-4">
            <div class="card-header">Data Utama</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
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

        {{-- Data Produk --}}
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
                        <div class="row g-3 card-body">
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

                        <div class="card-body row g-3" style="margin-top: -2rem;">
                            <div class="col-md-6">
                                <label>Jam Mulai</label>
                                <input type="time" class="form-control" name="details[{{ $i }}][start_time]">
                            </div>
                            <div class="col-md-6">
                                <label>Jam Selesai</label>
                                <input type="time" class="form-control" name="details[{{ $i }}][end_time]">
                            </div>
                        </div>

                        {{-- Process Steps --}}
                        @php
                        $steps = [
                        ['name' => 'DRYINGI', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        ['name' => 'DRYINGII', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        ['name' => 'DRYINGIII', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        ['name' => 'DRYINGIV', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        ['name' => 'DRYINGV', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        ['name' => 'DOOR OPENING SECTION 1', 'fields' => []],
                        ['name' => 'PUT CORE PROBE', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                        ['name' => 'SMOKING', 'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                        'room_temp_2']],
                        [
                        'name' => 'COOKINGI',
                        'fields' => ['time_minutes_1', 'time_minutes_2', 'air_circulation_1', 'air_circulation_2',
                        'room_temp_1', 'room_temp_2', 'product_temp_1', 'product_temp_2']
                        ],
                        [
                        'name' => 'COOKINGII',
                        'fields' => ['time_minutes_1', 'time_minutes_2', 'room_temp_1', 'room_temp_2', 'product_temp_1',
                        'product_temp_2', 'actual_product_temp']
                        ],
                        ['name' => 'STEAM SUCTION', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                        ['name' => 'DOOR OPENING SECTION 1', 'fields' => []],
                        ['name' => 'REMOVE CORE PROBE', 'fields' => ['time_minutes_1', 'time_minutes_2']],
                        ['name' => 'FURTHER TRANSPORT', 'fields' => []],
                        ];
                        @endphp

                        <div class="card mb-3">
                            <div class="card-header">TAHAP PEMASAKAN (Setting / Aktual)</div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered small">
                                        <thead class="text-center">
                                            <tr>
                                                <th style="min-width: 280px;">Nama Step</th>
                                                <th>Waktu 1</th>
                                                <th>Waktu 2</th>
                                                <th>Suhu Ruang 1</th>
                                                <th>Suhu Ruang 2</th>
                                                <th>Sirkulasi Udara 1</th>
                                                <th>Sirkulasi Udara 2</th>
                                                <th>Suhu Produk 1</th>
                                                <th>Suhu Produk 2</th>
                                                <th>Suhu Aktual Produk</th>
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
                                                @foreach(['time_minutes_1', 'time_minutes_2', 'room_temp_1',
                                                'room_temp_2', 'air_circulation_1', 'air_circulation_2',
                                                'product_temp_1', 'product_temp_2', 'actual_product_temp']
                                                as $field)
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


                        <!-- Sensory -->
                        <div class="card mb-3">
                            <div class="card-header">Pemeriksaan Sensorik</div>
                            <div class="card-body row g-3">
                                @foreach(['Kematangan' => 'ripeness', 'Aroma' => 'aroma', 'Rasa' => 'taste', 'Tekstur'
                                => 'texture', 'Warna' => 'color']
                                as $label => $field)
                                <div class="col-md-2">
                                    <label>{{ $label }}</label>
                                    <select name="details[{{ $i }}][sensory_check][{{ $field }}]" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="1">OK</option>
                                        <option value="0">Tidak OK</option>
                                    </select>
                                </div>
                                @endforeach

                                <div class="col-md-2">
                                    <label>Bisa / Tidak Bisa Di Ulir</label>
                                    <select name="details[{{ $i }}][sensory_check][can_be_twisted]"
                                        class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="1">Bisa</option>
                                        <option value="0">Tidak Bisa</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        {{-- Tahap Cooling --}}
                        @php
                        $coolingSteps = [
                        [
                        'name' => 'AIR COOLING WITH SHOWER INTER 1',
                        'fields' => ['time_minutes_1', 'time_minutes_2', 'rh_1', 'rh_2']
                        ],
                        ['name' => 'BLOWER SHOWER OUT SECTION 2', 'fields' => ['rh_1', 'rh_2']],
                        [
                        'name' => 'AIR COOLING WITH SHOWER INTER 2',
                        'fields' => ['time_minutes_1', 'time_minutes_2', 'rh_1', 'rh_2']
                        ],
                        ['name' => 'OUT TRANSPORT', 'fields' => ['rh_1', 'rh_2']],
                        [
                        'name' => 'SUHU PRODUK KELUAR',
                        'fields' => [
                        'product_temp_after_exit_1',
                        'product_temp_after_exit_2',
                        'product_temp_after_exit_3',
                        'avg_product_temp_after_exit'
                        ]
                        ],

                        ];
                        @endphp


                        <div class="card mb-3">
                            <div class="card-header">Tahap Cooling (Setting / Aktual)</div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered small">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" style="min-width: 280px;">Nama Tahap</th>
                                                <th colspan="2">Waktu (menit)</th>
                                                <th colspan="2">RH (%)</th>
                                                <th colspan="3">Suhu Pusat Produk Setelah Keluar (°C)</th>
                                                <th rowspan="2">Suhu Rata-rata</th>
                                                <th rowspan="2">Berat Mentah</th>
                                                <th rowspan="2">Berat Matang</th>
                                                <th rowspan="2">Loss (kg)</th>
                                                <th rowspan="2">Loss (%)</th>
                                            </tr>
                                            <tr>
                                                <th>1</th>
                                                <th>2</th>
                                                <th>1</th>
                                                <th>2</th>
                                                <th>1</th>
                                                <th>2</th>
                                                <th>3</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($coolingSteps as $index => $step)
                                            <tr>
                                                <td>
                                                    <input type="text" readonly class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][step_name]"
                                                        value="{{ $step['name'] }}">
                                                </td>

                                                {{-- time_minutes_1 --}}
                                                <td>
                                                    @if(in_array('time_minutes_1', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][time_minutes_1]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- time_minutes_2 --}}
                                                <td>
                                                    @if(in_array('time_minutes_2', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][time_minutes_2]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- rh_1 --}}
                                                <td>
                                                    @if(in_array('rh_1', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][rh_1]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- rh_2 --}}
                                                <td>
                                                    @if(in_array('rh_2', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][rh_2]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- product_temp_after_exit_1 --}}
                                                <td>
                                                    @if(in_array('product_temp_after_exit_1', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][product_temp_after_exit_1]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- product_temp_after_exit_2 --}}
                                                <td>
                                                    @if(in_array('product_temp_after_exit_2', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][product_temp_after_exit_2]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- product_temp_after_exit_3 --}}
                                                <td>
                                                    @if(in_array('product_temp_after_exit_3', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][product_temp_after_exit_3]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- avg_product_temp_after_exit --}}
                                                <td>
                                                    @if(in_array('avg_product_temp_after_exit', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][avg_product_temp_after_exit]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- raw_weight --}}
                                                <td>
                                                    @if(in_array('raw_weight', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][raw_weight]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- cooked_weight --}}
                                                <td>
                                                    @if(in_array('cooked_weight', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][cooked_weight]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- loss_kg --}}
                                                <td>
                                                    @if(in_array('loss_kg', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][loss_kg]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                                {{-- loss_percent --}}
                                                <td>
                                                    @if(in_array('loss_percent', $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][cooling_steps][{{ $index }}][loss_percent]">
                                                    @else
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        disabled>
                                                    @endif
                                                </td>

                                            </tr>
                                            @endforeach
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

<button class="btn btn-success mt-3">Simpan Laporan</button>
</form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fessmanStandards = @json($fessmanStandardMap);

    console.log('✅ Fessman Standards loaded:', fessmanStandards);

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-selector')) {
            const select = e.target;
            const productUuid = select.value;
            const index = select.dataset.index;

            console.log(`➡️ Product selected: ${productUuid}`);
            console.log(`➡️ Data index: ${index}`);

            const relatedInputs = document.querySelectorAll(`input[data-index="${index}"]`);
            console.log(`🧩 Related inputs found:`, relatedInputs.length);

            if (!fessmanStandards[productUuid]) {
                console.warn(`🚫 No FessmanStandard found for product: ${productUuid}`);
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

                const stepData = fessmanStandards[productUuid][step];
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