<div class="border rounded p-3 mb-3 rework-step-item">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Process</label>
            <select class="form-select form-control process-select" data-master="{{ $masterUuid }}"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][process_name]">
                <option value="">Pilih Process</option>
                @foreach(config('smokehouse.processes') as $process)
                <option value="{{ $process }}" {{ ($step->process_name ?? '')==$process?'selected':'' }}>
                    {{ $process }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Setting Temp</label>
            <input readonly class="form-control setting-temp"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][setting_temp]"
                value="{{ $step->setting_temp ?? '' }}">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Setting Time</label>
            <input readonly class="form-control setting-time"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][setting_time]"
                value="{{ $step->setting_time ?? '' }}">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Setting RH</label>
            <input readonly class="form-control setting-rh"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][setting_rh]"
                value="{{ $step->setting_rh ?? '' }}">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Setting CT</label>
            <input readonly class="form-control setting-ct"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][setting_ct]"
                value="{{ $step->setting_ct ?? '' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Actual Temp</label>
            <input class="form-control"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][actual_temp]"
                value="{{ $step->actual_temp ?? '' }}" placeholder="mis: 12.5">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Actual Time</label>
            <input class="form-control"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][actual_time]"
                value="{{ $step->actual_time ?? '' }}" placeholder="mis: 120">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Actual RH</label>
            <input class="form-control"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][actual_rh]"
                value="{{ $step->actual_rh ?? '' }}" placeholder="mis: 60">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Actual CT</label>
            <input class="form-control"
                name="details[{{ $index }}][reworks][{{ $reworkIndex }}][steps][{{ $stepIndex }}][actual_ct]"
                value="{{ $step->actual_ct ?? '' }}" placeholder="mis: 5">
        </div>
        <div class="col-md-1 d-flex align-items-end mb-3">
            <button type="button" class="btn btn-danger remove-rework-step">
                <i class="bx bx-trash">X</i>
            </button>
        </div>
    </div>
</div>