@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Laporan Verifikasi Pemasakan Fessman</h4>
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

    <form action="{{ route('report_fessman_cookings.update', $report->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Data Utama --}}
        <div class="card mb-4">
            <div class="card-header">Data Utama</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}"
                        required>
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}"
                        required>
                </div>
            </div>
        </div>

        {{-- Produk --}}
        <div class="accordion" id="produkAccordion">
            @for($i=0; $i<5; $i++) @php $detail=$report->details[$i] ?? null;
                $isOpen = $detail ? true : false;
                $steps = [
                ['name'=>'DRYINGI','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
                ['name'=>'DRYINGII','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
                ['name'=>'DRYINGIII','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
                ['name'=>'DRYINGIV','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
                ['name'=>'DRYINGV','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
                ['name'=>'DOOR OPENING SECTION 1','fields'=>[]],
                ['name'=>'PUT CORE PROBE','fields'=>['time_minutes_1','time_minutes_2']],
                ['name'=>'SMOKING','fields'=>['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2']],
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
                [
                'name' => 'DRYING',
                'fields' => ['time_minutes_1', 'time_minutes_2','air_circulation_1', 'air_circulation_2', 'room_temp_1', 'room_temp_2', 'product_temp_1',
                'product_temp_2', 'actual_product_temp']
                ],
                ['name'=>'STEAM SUCTION','fields'=>['time_minutes_1','time_minutes_2']],
                ['name'=>'DOOR OPENING SECTION 1','fields'=>[]],
                ['name'=>'REMOVE CORE PROBE','fields'=>['time_minutes_1','time_minutes_2']],
                ['name'=>'FURTHER TRANSPORT','fields'=>[]],
                ];

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
                'product_temp_after_exit_1','product_temp_after_exit_2','product_temp_after_exit_3','avg_product_temp_after_exit'
                ]
                ],
                ];

                @endphp

                <div class="accordion-item mb-2">
                    <h3 class="accordion-header">
                        <button
                            class="accordion-button {{ $isOpen ? '' : 'collapsed' }} bg-primary bg-gradient text-white fw-semibold"
                            type="button" data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}"
                            style="border: none; border-radius: .5rem; padding: .5rem;">
                            <i class="bi bi-box-seam me-2"></i> Data Produk #{{ $i+1 }}
                        </button>
                    </h3>
                    <div id="produk{{ $i }}" class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}"
                        data-bs-parent="#produkAccordion">
                        <div class="accordion-body card shadow">
                            <div class="row card-body" >
                                <div class="col-md-6">
                                    <label>Nomor Mesin Fessman</label>
                                    <input type="text" name="details[{{ $i }}][no_fessman]" class="form-control"
                                        value="{{ $detail->no_fessman ?? '' }}">
                                </div>
                            </div>

                            {{-- Info Produk --}}
                            <div class="row g-3 card-body mb-3" style="margin-top: -3rem;">
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <label>Kode Produksi</label>
                                    <input type="text" name="details[{{ $i }}][production_code]" class="form-control"
                                        value="{{ $detail->production_code ?? '' }}">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Untuk Kemasan (gr)</label>
                                    <input type="number" name="details[{{ $i }}][packaging_weight]" class="form-control"
                                        value="{{ $detail->packaging_weight ?? '' }}">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Jumlah Trolley</label>
                                    <input type="number" name="details[{{ $i }}][trolley_count]" class="form-control"
                                        value="{{ $detail->trolley_count ?? '' }}">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Jam Mulai</label>
                                    <input type="time" name="details[{{ $i }}][start_time]" class="form-control"
                                        value="{{ $detail->start_time ?? '' }}">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Jam Selesai</label>
                                    <input type="time" name="details[{{ $i }}][end_time]" class="form-control"
                                        value="{{ $detail->end_time ?? '' }}">
                                </div>

                            </div>

                            {{-- Process Steps --}}
                            <div class="card mb-3">
                                <div class="card-header">TAHAP PEMASAKAN (Setting / Aktual)</div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered small" style="table-layout:fixed; width:100%;">
                                            <thead class="text-center">
                                                <tr>
                                                    <th style="width:210px">Nama Step</th>
                                                    <th style="width:120px">Waktu 1</th>
                                                    <th style="width:120px">Waktu 2</th>
                                                    <th style="width:120px">Suhu Ruang 1</th>
                                                    <th style="width:120px">Suhu Ruang 2</th>
                                                    <th style="width:120px">Sirkulasi Udara 1</th>
                                                    <th style="width:120px">Sirkulasi Udara 2</th>
                                                    <th style="width:120px">Suhu Produk 1</th>
                                                    <th style="width:120px">Suhu Produk 2</th>
                                                    <th style="width:120px">Suhu Aktual Produk</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($steps as $index => $step)
                                                @php $stepData = $detail ?
                                                $detail->processSteps->firstWhere('step_name',$step['name']) : null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="text" readonly class="form-control form-control-sm"
                                                            name="details[{{ $i }}][process_steps][{{ $index }}][step_name]"
                                                            value="{{ strtoupper(str_replace(' ', '', $step['name'])) }}"
                                                            data-index="{{ $i }}"
                                                            data-step="{{ strtoupper(str_replace(' ', '', $step['name'])) }}">
                                                    </td>
                                                    @foreach(['time_minutes_1','time_minutes_2','room_temp_1','room_temp_2','air_circulation_1','air_circulation_2','product_temp_1','product_temp_2','actual_product_temp']
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
                                                        <input type="number" class="form-control form-control-sm"
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

                            <div class="card mb-3">
                                <div class="card-header">Pemeriksaan Sensorik</div>
                                <div class="card-body row g-3">
                                    @foreach(['Kematangan'=>'ripeness','Aroma'=>'aroma','Rasa'=>'taste','Tekstur'=>'texture','Warna'=>'color']
                                    as $label => $field)
                                    <div class="col-md-6">
                                        <label>{{ $label }}</label>
                                        <select name="details[{{ $i }}][sensory_check][{{ $field }}]"
                                            class="form-control mb-2">
                                            <option value="">-- Pilih --</option>
                                            <option value="1"
                                                {{ optional($detail?->sensoryCheck)->$field == 1 ? 'selected' : '' }}>OK
                                            </option>
                                            <option value="0"
                                                {{ optional($detail?->sensoryCheck)->$field == 0 ? 'selected' : '' }}>
                                                Tidak OK</option>
                                        </select>
                                    </div>
                                    @endforeach

                                    <div class="col-md-6">
                                        <label>Bisa / Tidak Bisa Di Ulir</label>
                                        <select name="details[{{ $i }}][sensory_check][can_be_twisted]"
                                            class="form-control">
                                            <option value="">-- Pilih --</option>
                                            <option value="1"
                                                {{ optional($detail?->sensoryCheck)->can_be_twisted == 1 ? 'selected' : '' }}>
                                                Bisa</option>
                                            <option value="0"
                                                {{ optional($detail?->sensoryCheck)->can_be_twisted == 0 ? 'selected' : '' }}>
                                                Tidak Bisa</option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            {{-- Cooling Steps --}}
                            <div class="card mb-3">
                                <div class="card-header">Tahap Cooling (Setting / Aktual)</div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered small" style="table-layout:fixed; width:100%;">
                                            <thead class="text-center">
                                                <tr>
                                                    <th rowspan="2" style="width: 300px;">Nama Tahap</th>
                                                    <th colspan="2" style="width:240px">Waktu (menit)</th>
                                                    <th colspan="2" style="width:240px">RH (%)</th>
                                                    <th colspan="3" style="width:360px">Suhu Pusat Produk Setelah Keluar (°C)</th>
                                                    <th rowspan="2" style="width:120px">Suhu Rata-rata</th>
                                                    <th rowspan="2" style="width:120px">Berat Mentah</th>
                                                    <th rowspan="2" style="width:120px">Berat Matang</th>
                                                    <th rowspan="2" style="width:120px">Loss (kg)</th>
                                                    <th rowspan="2" style="width:120px">Loss (%)</th>
                                                </tr>
                                                <tr>
                                                    <th style="width:120px">1</th>
                                                    <th style="width:120px">2</th>
                                                    <th style="width:120px">1</th>
                                                    <th style="width:120px">2</th>
                                                    <th style="width:120px">1</th>
                                                    <th style="width:120px">2</th>
                                                    <th style="width:120px">3</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($coolingSteps as $index => $step)
                                                @php
                                                $stepData = $detail ?
                                                $detail->coolingDowns->firstWhere('step_name',$step['name']) : null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="text" readonly class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooling_steps][{{ $index }}][step_name]"
                                                            value="{{ $step['name'] }}">
                                                    </td>

                                                    @foreach(['time_minutes_1','time_minutes_2','rh_1','rh_2',
                                                    'product_temp_after_exit_1','product_temp_after_exit_2','product_temp_after_exit_3',
                                                    'avg_product_temp_after_exit','raw_weight','cooked_weight','loss_kg','loss_percent']
                                                    as $field)
                                                    <td>
                                                        @if(in_array($field, $step['fields']))
                                                        <input type="number" step="any"
                                                            class="form-control form-control-sm"
                                                            name="details[{{ $i }}][cooling_steps][{{ $index }}][{{ $field }}]"
                                                            value="{{ $stepData ? $stepData->$field : '' }}">
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
// document.addEventListener('DOMContentLoaded', function() {
//     const fessmanStandards = @json($fessmanStandardMap);

//     console.log('✅ Fessman Standards loaded:', fessmanStandards);

//     document.addEventListener('change', function(e) {
//         if (e.target.classList.contains('product-selector')) {
//             const select = e.target;
//             const productUuid = select.value;
//             const index = select.dataset.index;

//             console.log(`➡️ Product selected: ${productUuid}`);
//             console.log(`➡️ Data index: ${index}`);

//             const relatedInputs = document.querySelectorAll(`input[data-index="${index}"]`);
//             console.log(`🧩 Related inputs found:`, relatedInputs.length);

//             if (!fessmanStandards[productUuid]) {
//                 console.warn(`🚫 No FessmanStandard found for product: ${productUuid}`);
//                 relatedInputs.forEach(input => {
//                     if (!input.disabled) input.value = '';
//                 });
//                 return;
//             }

//             relatedInputs.forEach(input => {
//                 const step = input.dataset.step;
//                 const field = input.dataset.field;

//                 console.log(`🔍 Input: step="${step}", field="${field}"`);

//                 if (!step || !field) {
//                     console.warn('⚠️ Missing step or field');
//                     return;
//                 }

//                 const stepData = fessmanStandards[productUuid][step];
//                 console.log(`📦 Step data for "${step}":`, stepData);

//                 if (stepData && stepData[field] !== undefined) {
//                     input.value = stepData[field];
//                     console.log(`✅ Set value for [${step}][${field}]: ${stepData[field]}`);
//                 } else {
//                     input.value = '';
//                     console.warn(`❓ Data not found for [${step}][${field}]`);
//                 }
//             });
//         }
//     });
// });
</script>

@endsection