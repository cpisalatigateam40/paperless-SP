<style>
    /* ---- Sensori Ok / Tidak Ok ---- */
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
    $steps    = \App\Models\RunStepWaterbath::PARAMETERS;
    $fields   = \App\Models\RunStepWaterbath::FIELDS;
    $sensor   = (int) old("runs.$i.sensor_ok", $run?->sensor_ok ?? true); // default Ok

    // input per sel; tipe mengikuti kolom (time / teks / angka 2 desimal)
    $cell = function (string $key, string $field) use ($i, $run) {
        $raw  = old("runs.$i.steps.$key.$field", $run?->step($key)?->$field);
        $name = "runs[$i][steps][$key][$field]";

        if (in_array($field, ['start_time', 'stop_time'], true)) {
            return '<input type="time" class="form-control form-control-sm text-center" name="' . $name . '"'
                . ' value="' . e(\App\Models\RunStepWaterbath::hm($raw)) . '">';
        }

        if ($field === 'remark') {
            return '<input type="text" maxlength="255" class="form-control form-control-sm" name="' . $name . '"'
                . ' placeholder="Opsional" value="' . e($raw) . '">';
        }

        return '<input type="number" step="0.01" inputmode="decimal" class="form-control form-control-sm text-center"'
            . ' name="' . $name . '" placeholder="Mis. 85.00" value="' . e($raw) . '">';
    };
@endphp

<div class="run-block wb-card card border mb-4">
    <input type="hidden" name="runs[{{ $i }}][uuid]" value="{{ old("runs.$i.uuid", $run?->uuid) }}">

    <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="run-title"><i class="fas fa-flask text-primary me-2"></i><span class="run-title-text">B. Hasil Verifikasi</span></strong>
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

            <div class="col-md-6">
                <label class="form-label">Initial Temperature</label>
                <div class="input-group">
                    <input type="number" step="0.01" inputmode="decimal" name="runs[{{ $i }}][initial_temp]"
                        class="form-control" placeholder="Mis. 12.00"
                        value="{{ old("runs.$i.initial_temp", $run?->initial_temp) }}">
                    <span class="input-group-text">°C</span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle text-center wb-table mb-3" style="min-width: 1100px;">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        @foreach($fields as $field => $label)
                            <th>{{ $label }}@if(!in_array($field, ['start_time', 'stop_time', 'remark'], true)) <small class="text-muted">(°C)</small>@endif</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($steps as $key => $label)
                    <tr>
                        <td class="text-start">
                            <span class="wb-no">{{ $loop->iteration }}</span>
                            <span class="fw-semibold">{{ $label }}</span>
                        </td>

                        @foreach($fields as $field => $fLabel)
                            @if(\App\Models\RunStepWaterbath::allows($key, $field))
                                <td>{!! $cell($key, $field) !!}</td>
                            @else
                                <td class="bg-light text-muted fst-italic"><small>(Tidak untuk diisi)</small></td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
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