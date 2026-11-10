@php
    $hm      = fn ($t) => $t ? substr($t, 0, 5) : null;   // 13:00:00 -> 13:00
    $p       = "details.$di.runs.$ri";                     // path untuk old()
    $n       = "details[$di][runs][$ri]";                  // path untuk name=""
    $byOrder = $run ? $run->steps->keyBy('step_order') : collect();
    $finish  = $byOrder->get(count($steps) + 1)?->finishStep;

    // Standar untuk JS: dari snapshot (edit) atau kosong (create, diisi AJAX dari master)
    $std = ['init_min' => $run?->initial_temp_min, 'init_max' => $run?->initial_temp_max, 'steps' => []];
    foreach ($byOrder as $order => $st) {
        if ($ss = $st->standardStep) {
            $std['steps'][$order] = [
                'set_time'       => $ss->set_time,
                'water_temp_min' => $ss->set_water_temp_min,
                'water_temp_max' => $ss->set_water_temp_max,
                'pressure_min'   => $ss->set_pressure_min,
                'pressure_max'   => $ss->set_pressure_max,
            ];
        }
    }

    $sv = old("$p.sensor_ok", is_null($run?->sensor_ok) ? null : (int) $run->sensor_ok);
    $sid = "sensor-{$di}-{$ri}";
@endphp

@push('styles')
    <style>
        .sensori-toggle {
    display: inline-flex;
    width: 100%;
    max-width: 260px;
    border: 1px solid #d5d9e2;
    border-radius: .5rem;
    background: #fff;
    overflow: hidden;
}

/* sembunyikan radio asli, tapi tetap bisa difokus keyboard */
.sensori-toggle input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.sensori-toggle label {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    margin: 0;
    padding: .5rem .75rem;
    font-size: .85rem;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    user-select: none;
    transition: background-color .15s, color .15s;
}

.sensori-toggle label + input + label {
    border-left: 1px solid #d5d9e2;
}

.sensori-toggle label:hover {
    background: #f5f6fa;
}

/* state terpilih */
.sensori-toggle input:checked + label.is-ok {
    background: #16a34a;
    color: #fff;
}

.sensori-toggle input:checked + label.is-no {
    background: #dc2626;
    color: #fff;
}

/* aksesibilitas keyboard */
.sensori-toggle input:focus-visible + label {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}
    </style>
@endpush

<div class="border rounded p-3 mb-3 run-item" data-std="{{ json_encode($std) }}">
    @if($run)
    <input type="hidden" name="{{ $n }}[uuid]" value="{{ $run->uuid }}">
    @endif

    {{-- Judul --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <i class="fas fa-flask text-primary me-2"></i>
            <h6 class="mb-0 run-title">B. Hasil Verifikasi</h6>
        </div>
        @if($ri !== 0)
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-run">
            <i class="fas fa-trash me-1"></i> Hapus
        </button>
        @endif
    </div>

    {{-- Chamber & initial temp --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nomor Retort Chamber</label>
            <input type="text" name="{{ $n }}[retort_chamber_number]" class="form-control" placeholder="Mis. 3"
                value="{{ old("$p.retort_chamber_number", $run?->retort_chamber_number) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Jumlah Trolly</label>
            <input type="number" min="0" name="{{ $n }}[trolley_count]" class="form-control" placeholder="Mis. 4"
                value="{{ old("$p.trolley_count", $run?->trolley_count) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Jumlah Tray</label>
            <input type="number" min="0" name="{{ $n }}[tray_count]" class="form-control" placeholder="Mis. 28"
                value="{{ old("$p.tray_count", $run?->tray_count) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Initial Temperature</label>
            <div class="input-group">
                <input type="number" step="0.01" name="{{ $n }}[initial_temp]" class="form-control inp-initial"
                    placeholder="Mis. 12"
                    value="{{ old("$p.initial_temp", $run?->initial_temp) }}">
                <span class="input-group-text">°C</span>
            </div>
            <small class="text-muted">Std: <span class="set-initial">-</span> °C</small>
        </div>
    </div>

    {{-- Jam proses --}}
    <div class="time-box p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3 col-6">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="{{ $n }}[start_time]" class="form-control inp-run-start"
                    value="{{ old("$p.start_time", $hm($run?->start_time)) }}">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="{{ $n }}[end_time]" class="form-control inp-run-end"
                    value="{{ old("$p.end_time", $hm($run?->end_time)) }}">
            </div>
            <div class="col-md-6">
                <span class="text-muted small d-block mb-1">Total Time</span>
                <span class="total-badge"><i class="far fa-clock me-1"></i><span class="total-time">-</span></span>
            </div>
        </div>
    </div>

    {{-- Tabel step --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle text-center table-steps mb-3">
            <thead>
                <tr>
                    <th rowspan="2" class="text-start" style="min-width: 200px;">Parameter</th>
                    <th colspan="2">Time (menit)</th>
                    <th colspan="2">Water Temp (°C)</th>
                    <th colspan="2">Pressure (Mpa)</th>
                </tr>
                <tr>
                    <th class="text-muted">Set</th><th>Act</th>
                    <th class="text-muted">Set</th><th>Act</th>
                    <th class="text-muted">Set</th><th>Act</th>
                </tr>
            </thead>
            <tbody>
                @foreach($steps as $order => $name)
                @php $ss = $byOrder->get($order)?->standardStep; @endphp
                <tr class="step-row" data-order="{{ $order }}">
                    <td class="text-start">
                        <span class="step-no">{{ $order }}</span>{{ $name }}
                    </td>
                    <td class="set-time col-set">-</td>
                    <td>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm inp-act-time"
                            name="{{ $n }}[steps][{{ $order }}][act_time]" placeholder="Menit"
                            value="{{ old("$p.steps.$order.act_time", $ss?->act_time) }}">
                    </td>
                    <td class="set-water col-set">-</td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm inp-water"
                            name="{{ $n }}[steps][{{ $order }}][water_temp]" placeholder="°C"
                            value="{{ old("$p.steps.$order.water_temp", $ss?->water_temp) }}">
                    </td>
                    <td class="set-pressure col-set">-</td>
                    <td>
                        <input type="number" step="0.001" min="0" class="form-control form-control-sm inp-pressure"
                            name="{{ $n }}[steps][{{ $order }}][pressure]" placeholder="Mpa"
                            value="{{ old("$p.steps.$order.pressure", $ss?->pressure) }}">
                    </td>
                </tr>
                @endforeach
                <tr class="finish-row">
                    <td class="text-start"><span class="step-no"><i class="fas fa-flag-checkered" style="font-size:.6rem"></i></span>Finish Produk</td>
                    <td colspan="5" class="text-end fw-semibold">Suhu Pusat Produk (°C)</td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm"
                            name="{{ $n }}[product_core_temp]" placeholder="°C"
                            value="{{ old("$p.product_core_temp", $finish?->product_core_temp) }}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Sensori & notes --}}
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label d-block">Sensori</label>
            <div class="sensori-toggle" role="radiogroup" aria-label="Sensori">
                <input type="radio" name="{{ $n }}[sensor_ok]" id="{{ $sid }}-ok" value="1"
                    @checked((string) $sv === '1')>
                <label for="{{ $sid }}-ok" class="is-ok">
                    <i class="fas fa-check"></i> Ok
                </label>

                <input type="radio" name="{{ $n }}[sensor_ok]" id="{{ $sid }}-no" value="0"
                    @checked((string) $sv === '0')>
                <label for="{{ $sid }}-no" class="is-no">
                    <i class="fas fa-times"></i> Tidak Ok
                </label>
            </div>
        </div>
        <div class="col-md-9">
            <label class="form-label">Notes</label>
            <textarea name="{{ $n }}[notes]" rows="2" class="form-control"
                placeholder="Mis. kejadian saat proses berlangsung">{{ old("$p.notes", $run?->notes) }}</textarea>
        </div>
    </div>
</div>