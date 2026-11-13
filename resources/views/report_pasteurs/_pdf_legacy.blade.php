@php
    $steps   = \App\Models\MasterPasteurStep::STEPS;
    $byOrder = $detail->steps->keyBy('step_order');
    $fin     = $byOrder->get(9)?->finishStep;

    $img = function ($path) {
        if (!$path) return null;
        $full = storage_path('app/public/' . $path);
        return file_exists($full)
            ? 'data:image/' . pathinfo($full, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($full))
            : null;
    };

    $qc   = $img($detail->qc_paraf);
    $prod = $img($detail->production_paraf);
@endphp

<div class="section-title">B. Hasil Verifikasi</div>
<table class="info" style="width: 100%;">
    <tr>
        <td style="width: 130px;">Jumlah Troly/Pack</td>
        <td>: {{ $detail->trolley_count ?? '-' }}</td>
    </tr>
    <tr>
        <td>Suhu Produk</td>
        <td>: {{ $detail->product_temp ?? '-' }}</td>
    </tr>
</table>

<table class="grid">
    <thead>
        <tr>
            <th style="width: 30%;">Parameter</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Temp. Air (°C)</th>
            <th>Pressure (MPa)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($steps as $order => $name)
        @php
            // Step 8 data lama tersimpan di tabel drainage
            $row = $order === 8
                ? $byOrder->get(8)?->drainageStep
                : $byOrder->get($order)?->standardStep;
        @endphp
        <tr>
            <td class="left">{{ $order }}. {{ $name }}</td>
            <td>{{ $row?->start_time ?? '-' }}</td>
            <td>{{ $row?->end_time ?? '-' }}</td>
            <td>{{ $order === 8 ? '-' : ($row?->water_temp ?? '-') }}</td>
            <td>{{ $order === 8 ? '-' : ($row?->pressure ?? '-') }}</td>
        </tr>
        @endforeach
        <tr>
            <td class="left">9. Finish Produk (Suhu Pusat)</td>
            <td colspan="4">{{ $fin?->product_core_temp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="left">Sortasi</td>
            <td colspan="4">{{ $fin?->sortation ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<table style="width: 50%; border: none;">
    <tr>
        <td style="border: none;">
            Paraf QC<br>
            @if($qc)<img src="{{ $qc }}" width="60">@else - @endif
        </td>
        <td style="border: none;">
            Paraf Produksi<br>
            @if($prod)<img src="{{ $prod }}" width="60">@else - @endif
        </td>
    </tr>
</table>