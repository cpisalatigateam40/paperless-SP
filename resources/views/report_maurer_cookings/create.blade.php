@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Buat Laporan Pemasakan Rumah Asap, Showering, dan Cooling Down Maurer</h4>
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
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
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
            @for($i=0; $i<5; $i++) <div class="accordion-item mb-2">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed bg-primary bg-gradient text-white fw-semibold"
                        type="button" data-bs-toggle="collapse" data-bs-target="#produk{{ $i }}"
                        style="border: none; border-radius: .5rem; padding: .5rem;">
                        <i class="bi bi-box-seam me-2"></i> Data Produk #{{ $i+1 }}
                    </button>
                </h3>
                <div id="produk{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#produkAccordion">
                    <div class="accordion-body card shadow">

                        {{-- Info Produk --}}
                        <div class="row g-3 mb-3 card-body">
                            <div class="col-md-4">
                                <label>Nama Produk</label>
                                <select name="details[{{ $i }}][product_uuid]" class="form-control">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
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
                        ['name'=>'SHOWERING','fields'=>['time_minutes_1','time_minutes_2']],
                        ['name'=>'WARMING','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'DRYINGI','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'DRYINGII','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'DRYINGIII','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'SMOKING','fields'=>['room_temperature_1','room_temperature_2','rh_1','rh_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'COOKING','fields'=>['room_temperature_1','room_temperature_2','product_temperature_1','product_temperature_2','time_minutes_1','time_minutes_2']],
                        ['name'=>'EVAKUASI','fields'=>['time_minutes_1','time_minutes_2']],
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
                                                <th>Suhu Ruang 1</th>
                                                <th>Suhu Ruang 2</th>
                                                <th>RH 1</th>
                                                <th>RH 2</th>
                                                <th>Waktu (menit) 1</th>
                                                <th>Waktu (menit) 2</th>
                                                <th>Suhu Produk 1</th>
                                                <th>Suhu Produk 2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($steps as $index=>$step)
                                            <tr>
                                                <td>
                                                    <input type="text" readonly class="form-control form-control-sm"
                                                        name="details[{{ $i }}][process_steps][{{ $index }}][step_name]"
                                                        value="{{ $step['name'] }}">
                                                </td>
                                                @foreach(['room_temperature_1','room_temperature_2','rh_1','rh_2',
                                                'time_minutes_1','time_minutes_2','product_temperature_1','product_temperature_2']
                                                as $field)
                                                <td>
                                                    @if(in_array($field, $step['fields']))
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][process_steps][{{ $index }}][{{ $field }}]">
                                                    @else
                                                    {{-- field tidak aktif, tambahkan input kosong dan disable --}}
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

                        {{-- Row tambahan LAMA PROSES --}}
                        <div class="card mb-3">
                            <div class="card-header">Lama Proses Total</div>
                            <div class="card-body row g-3">
                                <div class="col-md-6">
                                    <label>Jam Mulai</label>
                                    <input type="time" class="form-control"
                                        name="details[{{ $i }}][total_process_time][start_time]">
                                </div>
                                <div class="col-md-6">
                                    <label>Jam Selesai</label>
                                    <input type="time" class="form-control"
                                        name="details[{{ $i }}][total_process_time][end_time]">
                                </div>
                            </div>
                        </div>

                        {{-- Thermocouple --}}
                        <div class="card mb-3">
                            <div class="card-header">Posisi Thermocouple</div>
                            <div class="card-body col-md-6">
                                @for($t=0;$t<1;$t++) <input type="text" class="form-control mb-2"
                                    placeholder="Masukkan Posisi"
                                    name="details[{{ $i }}][thermocouple_positions][{{ $t }}][position_info]">
                                    @endfor
                            </div>
                        </div>

                        {{-- Sensory --}}
                        <div class="card mb-3">
                            <div class="card-header">Pemeriksaan Sensorik</div>
                            <div class="card-body row g-3">
                                @foreach(['Kematangan'=>'ripeness','Aroma'=>'aroma','Tekstur'=>'texture','Warna'=>'color']
                                as $label=>$field)
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
                                                        name="details[{{ $i }}][showering_cooling_down][room_temp_1]">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][room_temp_2]">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Suhu Produk / CT --}}
                                            <tr>
                                                <td>Suhu Produk / CT (°C)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_1]">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_2]">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Waktu --}}
                                            <tr>
                                                <td>Waktu (menit)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][time_minutes_1]">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][time_minutes_2]">
                                                </td>
                                                <td class="bg-light text-center text-muted small">–</td>
                                            </tr>

                                            {{-- Suhu pusat produk setelah keluar --}}
                                            <tr>
                                                <td>Suhu pusat produk setelah keluar (°C)</td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_1]">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_2]">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][product_temp_after_exit_3]">
                                                </td>
                                            </tr>

                                            {{-- Suhu rata-rata pusat produk setelah keluar --}}
                                            <tr>
                                                <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                                                <td colspan="3">
                                                    <input type="number" step="any" class="form-control form-control-sm"
                                                        name="details[{{ $i }}][showering_cooling_down][avg_product_temp_after_exit]">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Cooking Losses --}}
                        <div class="card mb-3">
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
                                                <th style="width: 20%;">Loss (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($l=0;$l<1;$l++) <tr>
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