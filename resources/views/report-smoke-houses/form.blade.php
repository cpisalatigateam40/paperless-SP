<div class="card-body">

    {{-- Header Report --}}
    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Tanggal
            </label>

            <input type="date" name="date" class="form-control"
                value="{{ old('date', isset($report) ? $report->date : date('Y-m-d')) }}" required>

        </div>

        <div class="col-md-6">
            <label>Shift</label>
            <input type="text" name="shift" class="form-control"
                value="{{ old('shift', $report->shift ?? session('shift_number') . '-' . session('shift_group')) }}">
        </div>
    </div>

    <hr>

    <div id="detailContainer" data-index="{{ isset($report) ? $report->details->count() : 1 }}"
        data-route-machines="{{ route('report-smoke-houses.machines', ['product_uuid' => '__PRODUCT__']) }}"
        data-route-steps="{{ route('report-smoke-houses.master-steps', ['master_uuid' => '__MASTER__']) }}">

        @if(isset($report))
        @foreach($report->details as $i => $detail)
        @include('report-smoke-houses.partials.detail', ['index' => $i, 'detail' => $detail])
        @endforeach
        @else
        @include('report-smoke-houses.partials.detail', ['index' => 0])
        @endif

    </div>

    <template id="detail-block-template">
        @include('report-smoke-houses.partials.detail', ['index' => '__INDEX__'])
    </template>

    <hr>

    <div class="mb-3">

        <label class="form-label">

            Catatan

        </label>

        <textarea name="notes" rows="3" class="form-control">{{ old('notes',$report->notes ?? '') }}</textarea>

    </div>

</div>

<div class="card-footer text-end">

    <a href="{{ route('report-smoke-houses.index') }}" class="btn btn-secondary">

        Kembali

    </a>

    <button class="btn btn-primary">

        Simpan

    </button>

</div>

@push('scripts')
<script src="{{ asset('js/report-smoke-house-form.js') }}"></script>
@endpush