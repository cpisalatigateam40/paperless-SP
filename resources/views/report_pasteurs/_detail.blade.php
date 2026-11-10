@php
    $MPS = \App\Models\MasterPasteurStep::class;
    $steps = $MPS::STEPS;
    $finishOrder = count($steps) + 1;
    $red = fn ($out) => $out ? 'text-danger fw-bold' : '';
@endphp

<div class="p-2">
@foreach($report->details as $detail)
    <div class="border rounded mb-3">
        {{-- Header produk --}}
        <div class="bg-light p-2 border-bottom">
            <strong>{{ $detail->product->product_name ?? '-' }}</strong>
            -
            {{ !empty($detail->for_packaging_gr) ? $detail->for_packaging_gr : ($detail->product->nett_weight ?? '-') }} g
            <span class="ms-3">Kode: {{ $detail->product_code ?? '-' }}</span>
            <span class="ms-3">Program: {{ $detail->program_number ?? '-' }}</span>
        </div>

        <div class="p-2">
        @if($detail->runs->isNotEmpty())
            {{-- ===================== FORMAT BARU ===================== --}}
            @foreach($detail->runs as $run)
                @php
                    $byOrder = $run->steps->keyBy('step_order');
                    $finish  = $byOrder->get($finishOrder)?->finishStep;
                    $total   = $run->total_minutes;
                @endphp

                <h6 class="mt-2">
                    {{ $run->attempt == 1 ? 'Hasil Verifikasi' : 'Pasteurisasi Ulang' . ($run->attempt > 2 ? ' #' . ($run->attempt - 1) : '') }}
                </h6>

                <div class="row mb-2">
                    <div class="col-md-3"><small class="text-muted d-block">Nomor Retort Chamber</small>{{ $run->retort_chamber_number ?? '-' }}</div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Jumlah Trolly (Tray)</small>
                        {{ $run->trolley_count ?? '-' }} trolly ({{ $run->tray_count ?? '-' }} tray)
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Time</small>
                        @if($total !== null)
                            {{ substr($run->start_time, 0, 5) }} - {{ substr($run->end_time, 0, 5) }} ({{ $total }} menit)
                        @else
                            -
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Initial Temperature</small>
                        <span class="{{ $red($MPS::outOfStd($run->initial_temp, $run->initial_temp_min, $run->initial_temp_max)) }}">
                            {{ $MPS::fmt($run->initial_temp) ?? '-' }}°C
                        </span>
                        <small class="text-muted">(Std: {{ $MPS::rangeLabel($run->initial_temp_min, $run->initial_temp_max) }})</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle mb-2">
                        <thead>
                            <tr>
                                <th class="text-start">Parameter</th>
                                <th>Set Time<br>(menit)</th>
                                <th>Act Time<br>(menit)</th>
                                <th>Set Water Temp<br>(°C)</th>
                                <th>Act Water Temp<br>(°C)</th>
                                <th>Set Pressure<br>(Mpa)</th>
                                <th>Act Pressure<br>(Mpa)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($steps as $order => $name)
                            @php $s = $byOrder->get($order)?->standardStep; @endphp
                            <tr>
                                <td class="text-start">{{ $order }}. {{ $name }}</td>
                                <td>{{ $MPS::fmt($s?->set_time) ?? '-' }}</td>
                                <td class="{{ $red($MPS::outOfStd($s?->act_time, $s?->set_time, null, 0)) }}">
                                    {{ $MPS::fmt($s?->act_time) ?? '-' }}
                                </td>
                                <td>{{ $MPS::rangeLabel($s?->set_water_temp_min, $s?->set_water_temp_max) }}</td>
                                <td class="{{ $red($MPS::outOfStd($s?->water_temp, $s?->set_water_temp_min, $s?->set_water_temp_max)) }}">
                                    {{ $MPS::fmt($s?->water_temp) ?? '-' }}
                                </td>
                                <td>{{ $MPS::rangeLabel($s?->set_pressure_min, $s?->set_pressure_max) }}</td>
                                <td class="{{ $red($MPS::outOfStd($s?->pressure, $s?->set_pressure_min, $s?->set_pressure_max)) }}">
                                    {{ $MPS::fmt($s?->pressure) ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-start">Finish Produk</td>
                                <td colspan="5" class="text-end">Suhu Pusat Produk (°C)</td>
                                <td>{{ $MPS::fmt($finish?->product_core_temp) ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-2">
                    <strong>Sensori:</strong>
                    @if(is_null($run->sensor_ok))
                        -
                    @else
                        <span class="badge {{ $run->sensor_ok ? 'bg-success' : 'bg-danger' }}">
                            {{ $run->sensor_ok ? 'Ok' : 'Tidak Ok' }}
                        </span>
                    @endif
                    <span class="ms-3"><strong>Notes:</strong> {{ $run->notes ?: '-' }}</span>
                </div>
            @endforeach

        @else
            @include('report_pasteurs._detail_legacy', ['detail' => $detail])
        @endif
        </div>
    </div>
@endforeach

<!-- @can('create report')
<div class="d-flex justify-content-end mt-2">
    <a href="{{ route('report_pasteurs.add_detail', $report->uuid) }}"
        class="btn btn-outline-secondary btn-sm">Tambah Detail</a>
</div>
@endcan -->
</div>