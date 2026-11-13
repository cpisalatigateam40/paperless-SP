@php
    $MPS     = \App\Models\MasterPasteurStep::class;
    $steps   = $MPS::STEPS;
    $byOrder = $run->steps->keyBy('step_order');
    $finish  = $byOrder->get(count($steps) + 1)?->finishStep;
    $total   = $run->total_minutes;
    $dot     = fn ($t) => str_replace(':', '.', substr($t, 0, 5));
    $title   = $run->attempt == 1
        ? 'B. Hasil Verifikasi'
        : 'Pasteurisasi Ulang' . ($run->attempt > 2 ? ' #' . ($run->attempt - 1) : '');
    $initRed = $MPS::outOfStd($run->initial_temp, $run->initial_temp_min, $run->initial_temp_max);
@endphp

<div class="section-title">{{ $title }}</div>
<table class="info" style="width: 100%;">
    <tr>
        <td style="width: 130px;">Nomor Retort Chamber</td>
        <td>: {{ $run->retort_chamber_number ?? '-' }}</td>
    </tr>
    <tr>
        <td>Jumlah Trolly (Tray)</td>
        <td>: {{ $run->trolley_count ?? '-' }} trolly ({{ $run->tray_count ?? '-' }} tray)</td>
    </tr>
    <tr>
        <td>Total Time</td>
        <td>:
            @if($total !== null)
                {{ $dot($run->start_time) }} - {{ $dot($run->end_time) }} ({{ $total }} menit)
            @else
                -
            @endif
        </td>
    </tr>
    <tr>
        <td>Initial Temperature</td>
        <td>: <span class="{{ $initRed ? 'red' : '' }}">{{ $MPS::fmt($run->initial_temp) ?? '-' }}&deg;C</span></td>
    </tr>
</table>

<table class="grid">
    <thead>
        <tr>
            <th style="width: 20%;">Parameter</th>
            <th>Set. Time<br>(minutes)</th>
            <th>Act. Time<br>(minutes)</th>
            <th>Set. Water Temp.<br>(&deg;C)</th>
            <th>Act. Water Temp.<br>(&deg;C)</th>
            <th>Set. Pressure<br>(Mpa)</th>
            <th>Act. Pressure<br>(Mpa)</th>
            <th>Suhu Pusat Produk</th>
        </tr>
    </thead>
    <tbody>
        @foreach($steps as $order => $name)
        @php $s = $byOrder->get($order)?->standardStep; @endphp
        <tr>
            <td class="left">{{ $order }}. {{ $name }}</td>
            <td>{{ $MPS::fmt($s?->set_time) ?? '-' }}</td>
            <td class="{{ $MPS::outOfStd($s?->act_time, $s?->set_time, null, 0) ? 'red' : '' }}">
                {{ $MPS::fmt($s?->act_time) ?? '-' }}
            </td>
            <td>{{ $MPS::rangeLabel($s?->set_water_temp_min, $s?->set_water_temp_max) }}</td>
            <td class="{{ $MPS::outOfStd($s?->water_temp, $s?->set_water_temp_min, $s?->set_water_temp_max) ? 'red' : '' }}">
                {{ $MPS::fmt($s?->water_temp) ?? '-' }}
            </td>
            <td>{{ $MPS::rangeLabel($s?->set_pressure_min, $s?->set_pressure_max) }}</td>
            <td class="{{ $MPS::outOfStd($s?->pressure, $s?->set_pressure_min, $s?->set_pressure_max) ? 'red' : '' }}">
                {{ $MPS::fmt($s?->pressure) ?? '-' }}
            </td>
            <td class="na">(Tidak untuk diisi)</td>
        </tr>
        @endforeach
        <tr>
            <td class="left">Finish Produk</td>
            @for($i = 0; $i < 6; $i++)
            <td class="na">(Tidak untuk diisi)</td>
            @endfor
            <td>{{ $MPS::fmt($finish?->product_core_temp) ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<div>Sensori: {{ is_null($run->sensor_ok) ? '-' : ($run->sensor_ok ? 'Ok' : 'Tidak Ok') }}</div>
<div style="margin-bottom: 10px;">Notes: {{ $run->notes ?: '-' }}</div>