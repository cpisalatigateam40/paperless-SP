@php
    $steps   = \App\Models\MasterPasteurStep::STEPS;
    $byOrder = $detail->steps->keyBy('step_order');
@endphp

{{-- ===================== FORMAT LAMA ===================== --}}
@php $byOrder = $detail->steps->keyBy('step_order'); @endphp

<div class="row mb-2">
    <div class="col-md-4"><small class="text-muted d-block">Jumlah Troly/Pack</small>{{ $detail->trolley_count ?? '-' }}</div>
    <div class="col-md-4"><small class="text-muted d-block">Suhu Produk</small>{{ $detail->product_temp ?? '-' }}</div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm text-center align-middle mb-2">
        <thead>
            <tr>
                <th class="text-start">Parameter</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Temp. Air (°C)</th>
                <th>Pressure (MPa)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($steps as $order => $name)
            @php
                // Step 8 data lama ada di tabel drainage
                $row = $order === 8
                    ? $byOrder->get(8)?->drainageStep
                    : $byOrder->get($order)?->standardStep;
            @endphp
            <tr>
                <td class="text-start">{{ $order }}. {{ $name }}</td>
                <td>{{ $row?->start_time ?? '-' }}</td>
                <td>{{ $row?->end_time ?? '-' }}</td>
                <td>{{ $order === 8 ? '-' : ($row?->water_temp ?? '-') }}</td>
                <td>{{ $order === 8 ? '-' : ($row?->pressure ?? '-') }}</td>
            </tr>
            @endforeach
            @php $fin = $byOrder->get(9)?->finishStep; @endphp
            <tr>
                <td class="text-start">9. Finish Produk</td>
                <td colspan="2" class="text-end">Suhu Pusat Produk</td>
                <td colspan="2">{{ $fin?->product_core_temp ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-start">Sortasi</td>
                <td colspan="4">{{ $fin?->sortation ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="d-flex gap-4">
    <div>
        <small class="text-muted d-block">Paraf QC</small>
        @if($detail->qc_paraf)
            <img src="{{ asset('storage/' . $detail->qc_paraf) }}" alt="QC" width="60">
        @else - @endif
    </div>
    <div>
        <small class="text-muted d-block">Paraf Produksi</small>
        @if($detail->production_paraf)
            <img src="{{ asset('storage/' . $detail->production_paraf) }}" alt="Produksi" width="60">
        @else - @endif
    </div>
</div>