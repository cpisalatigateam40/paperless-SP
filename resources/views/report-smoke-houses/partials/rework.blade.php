@php
$isEdit = isset($rework) && $rework !== null;
@endphp

<div class="card border shadow-sm mb-3 rework-item" data-rework-index="{{ $reworkIndex }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Cooking Ulang</strong>
        <button type="button" class="btn btn-danger btn-sm remove-rework">
            <i class="bx bx-trash">X</i>
        </button>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Smoke House</label>
                <input type="number" class="form-control"
                    name="details[{{ $index }}][reworks][{{ $reworkIndex }}][smoke_house_no]"
                    value="{{ $rework->smoke_house_no ?? '' }}" placeholder="mis: 2">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Trolley</label>
                <input type="number" class="form-control"
                    name="details[{{ $index }}][reworks][{{ $reworkIndex }}][trolley_count]"
                    value="{{ $rework->trolley_count ?? '' }}" placeholder="mis: 5">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Stick</label>
                <input type="number" class="form-control"
                    name="details[{{ $index }}][reworks][{{ $reworkIndex }}][stick_count]"
                    value="{{ $rework->stick_count ?? '' }}" placeholder="mis: 10">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Process</label>
                <input type="datetime-local" class="form-control"
                    name="details[{{ $index }}][reworks][{{ $reworkIndex }}][start_process]"
                    value="{{ isset($rework->start_process) ? \Carbon\Carbon::parse($rework->start_process)->format('Y-m-d\TH:i') : '' }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">End Process</label>
                <input type="datetime-local" class="form-control"
                    name="details[{{ $index }}][reworks][{{ $reworkIndex }}][end_process]"
                    value="{{ isset($rework->end_process) ? \Carbon\Carbon::parse($rework->end_process)->format('Y-m-d\TH:i') : '' }}">
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between mb-3">
            <strong>Process</strong>
            <button type="button" class="btn btn-primary btn-sm add-rework-step">
                <i class="bx bx-plus"></i> Tambah Process
            </button>
        </div>

        <div class="rework-step-container">
            @if($isEdit)
            @foreach($rework->steps as $stepIndex => $step)
            @include('report-smoke-houses.partials.rework-step', [
            'index' => $index,
            'reworkIndex' => $reworkIndex,
            'stepIndex' => $stepIndex,
            'step' => $step,
            'masterUuid' => $masterUuid,
            ])
            @endforeach
            @endif
        </div>

        <template class="tpl-rework-step">
            @include('report-smoke-houses.partials.rework-step', [
            'index' => $index,
            'reworkIndex' => $reworkIndex,
            'stepIndex' => '__SIDX__',
            'step' => null,
            'masterUuid' => $masterUuid,
            ])
        </template>
    </div>
</div>