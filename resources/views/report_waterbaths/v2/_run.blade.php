<style>
    .sensori-toggle { display: inline-flex; }
.sensori-toggle input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.sensori-toggle label {
    margin: 0; padding: .4rem 1.1rem; cursor: pointer; user-select: none;
    border: 1px solid #ced4da; background: #fff; color: #5a5c69;
    font-size: .875rem; font-weight: 600; transition: all .15s;
}
.sensori-toggle label.is-ok { border-radius: .375rem 0 0 .375rem; }
.sensori-toggle label.is-no { border-radius: 0 .375rem .375rem 0; border-left: 0; }
.sensori-toggle input:checked + label.is-ok { background: #16a34a; border-color: #16a34a; color: #fff; }
.sensori-toggle input:checked + label.is-no { background: #dc3545; border-color: #dc3545; color: #fff; }
.sensori-toggle input:focus-visible + label { box-shadow: 0 0 0 .2rem rgba(78, 91, 216, .25); }
</style>

@php
    // $i = index array (atau '__INDEX__' untuk template JS), $run = RunWaterbath|null
    $parameters = \App\Models\MasterWaterbathParameter::PARAMETERS;
    $snapFields = ['set_temp_min', 'set_temp_max', 'set_time', 'std_ct_min', 'std_ct_max'];
    $sensor     = (int) old("runs.$i.sensor_ok", $run?->sensor_ok ?? true); // default Ok

    // Input kolom Act. data-min / data-max = nama field snapshot standarnya (untuk cek merah)
    $act = function (string $key, string $field, string $min, ?string $max, string $ph) use ($i, $run) {
        $val = old("runs.$i.parameters.$key.$field", $run?->parameter($key)?->$field);

        return '<input type="number" step="0.01" inputmode="decimal"'
            . ' class="form-control form-control-sm text-center rng-act"'
            . ' name="runs[' . $i . '][parameters][' . $key . '][' . $field . ']"'
            . ' data-min="' . $min . '"' . ($max ? ' data-max="' . $max . '"' : '')
            . ' placeholder="' . e($ph) . '" value="' . e($val) . '">';
    };
@endphp

<div class="run-block wb-card card border mb-4">
    <input type="hidden" name="runs[{{ $i }}][uuid]" value="{{ old("runs.$i.uuid", $run?->uuid) }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="run-title"><i class="fas fa-flask text-primary mr-2"></i><span class="run-title-text">Hasil Verifikasi</span></strong>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger run-flag d-none"></span>
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-run d-none">
                <i class="fas fa-trash-alt"></i> Hapus
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nomor Waterbath</label>
                <input type="text" name="runs[{{ $i }}][waterbath_number]" class="form-control"
                    placeholder="Mis. 1"
                    value="{{ old("runs.$i.waterbath_number", $run?->waterbath_number) }}">
            </div>

            <div class="col-md-6" data-scope="initial">
                <label class="form-label">Initial Temperature</label>
                <div class="input-group">
                    <input type="number" step="0.01" inputmode="decimal"
                        name="runs[{{ $i }}][initial_temp]"
                        class="form-control rng-act" data-min="initial_temp_min" data-max="initial_temp_max"
                        placeholder="Mis. 12.00"
                        value="{{ old("runs.$i.initial_temp", $run?->initial_temp) }}">
                    <span class="input-group-text">°C</span>
                </div>
                <small class="text-muted">
                    Std: <span class="rng-text" data-min="initial_temp_min" data-max="initial_temp_max">-</span> °C
                </small>

                @foreach(['initial_temp_min', 'initial_temp_max'] as $f)
                    <input type="hidden" name="runs[{{ $i }}][{{ $f }}]" data-snap="{{ $f }}"
                        value="{{ old("runs.$i.$f", $run?->$f) }}">
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle text-center wb-table mb-3">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">Parameter</th>
                        <th colspan="2">Temp (°C)</th>
                        <th colspan="2">Time (menit)</th>
                        <th colspan="2">CT Prod. (°C)</th>
                    </tr>
                    <tr>
                        <th class="wb-sub">Set</th><th class="wb-sub">Act</th>
                        <th class="wb-sub">Set</th><th class="wb-sub">Act</th>
                        <th class="wb-sub">Std</th><th class="wb-sub">Act</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parameters as $key => $label)
                    @php
                        $p        = $run?->parameter($key);
                        $name     = "runs[$i][parameters][$key]";
                        $isDrying = $key === 'drying';
                    @endphp

                    <tr data-scope="{{ $key }}">
                        <td class="text-start" style="text-align: left !important;">
                            <span class="wb-no">{{ $loop->iteration }}</span>
                            <span class="fw-semibold">{{ $label }}</span>

                            {{-- snapshot standar dari master --}}
                            @foreach($snapFields as $f)
                                <input type="hidden" name="{{ $name }}[{{ $f }}]" data-snap="{{ $f }}"
                                    value="{{ old("runs.$i.parameters.$key.$f", $p?->$f) }}">
                            @endforeach
                            @if($isDrying)
                                @foreach(['cold_zone_set_min', 'cold_zone_set_max'] as $f)
                                    <input type="hidden" name="{{ $name }}[{{ $f }}]" data-snap="{{ $f }}"
                                        value="{{ old("runs.$i.parameters.$key.$f", $p?->$f) }}">
                                @endforeach
                            @endif
                        </td>

                        {{-- Set Temp --}}
                        <td class="wb-set">
                            @if($isDrying)
                                <div class="small text-muted">Hot zone</div>
                                <span class="rng-text" data-min="set_temp_min" data-max="set_temp_max">-</span>
                                <div class="small text-muted mt-1">Cold zone</div>
                                <span class="rng-text" data-min="cold_zone_set_min" data-max="cold_zone_set_max">-</span>
                            @else
                                <span class="rng-text" data-min="set_temp_min" data-max="set_temp_max">-</span>
                            @endif
                        </td>

                        {{-- Act Temp --}}
                        <td>
                            @if($isDrying)
                                <div class="mb-1">{!! $act($key, 'act_temp', 'set_temp_min', 'set_temp_max', 'Hot zone') !!}</div>
                                {!! $act($key, 'cold_zone_act', 'cold_zone_set_min', 'cold_zone_set_max', 'Cold zone') !!}
                            @else
                                {!! $act($key, 'act_temp', 'set_temp_min', 'set_temp_max', 'Mis. 90.00') !!}
                            @endif
                        </td>

                        {{-- Time --}}
                        <td class="wb-set"><span class="rng-text" data-min="set_time">-</span></td>
                        <td>{!! $act($key, 'act_time', 'set_time', null, 'Mis. 30') !!}</td>

                        {{-- CT Prod --}}
                        <td class="wb-set"><span class="rng-text" data-min="std_ct_min" data-max="std_ct_max">-</span></td>
                        <td>{!! $act($key, 'act_ct', 'std_ct_min', 'std_ct_max', 'Mis. 72.00') !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row g-3 mb-3 mt-3">
            <div class="col-md-6">
                <label class="form-label d-block">Sensori</label>
                <div class="sensori-toggle" role="radiogroup" aria-label="Sensori">
                    <input type="radio" name="runs[{{ $i }}][sensor_ok]"
                        id="sensor-{{ $i }}-ok" value="1" @checked($sensor === 1)>
                    <label for="sensor-{{ $i }}-ok" class="is-ok">
                        <i class="fas fa-check"></i> Ok
                    </label>

                    <input type="radio" name="runs[{{ $i }}][sensor_ok]"
                        id="sensor-{{ $i }}-no" value="0" @checked($sensor === 0)>
                    <label for="sensor-{{ $i }}-no" class="is-no">
                        <i class="fas fa-times"></i> Tidak Ok
                    </label>
                </div>
            </div>
        </div>

        <div>
            <label class="form-label">Notes</label>
            <textarea name="runs[{{ $i }}][notes]" rows="2" class="form-control"
                placeholder="Mis. suhu drop karena listrik mati, dilakukan pasteurisasi ulang">{{ old("runs.$i.notes", $run?->notes) }}</textarea>
        </div>
    </div>
</div>