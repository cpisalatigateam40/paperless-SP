@php
    $standardSteps = [
        1 => 'Water Injection',
        2 => 'Up Temperature',
        3 => 'Pasteurisasi',
        4 => 'Hot Water Recycling',
        5 => 'Cooling Water Injection',
        6 => 'Cooling Constant Temp.',
        7 => 'Raw Cooling Water',
    ];

    $stepFields = [
        'Jam Mulai (menit)' => 'start_time',
        'Jam Selesai (menit)' => 'end_time',
        'Temp. Air (°C)' => 'water_temp',
        'Pressure (MPa)' => 'pressure',
    ];

    $colCount = $details->count();
@endphp

@if ($colCount === 0)
    <div class="text-muted small">Tidak ada detail.</div>
@else
<div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
        <thead class="text-center align-middle">
            <tr>
                <th style="width: 220px;">Keterangan</th>
                @foreach ($details as $detail)
                    <th>
                        {{ $detail->product->product_name ?? '-' }} -
                        {{ !empty($detail->for_packaging_gr) ? $detail->for_packaging_gr : ($detail->product->nett_weight ?? '-') }} g
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Info produk --}}
            <tr><td>Nomor Program</td>@foreach ($details as $d)<td>{{ $d->program_number ?? '-' }}</td>@endforeach</tr>
            <tr><td>Kode Produk</td>@foreach ($details as $d)<td>{{ $d->product_code ?? '-' }}</td>@endforeach</tr>
            <tr><td>Untuk Kemasan (gr)</td>@foreach ($details as $d)<td>{{ $d->for_packaging_gr ?? '-' }}</td>@endforeach</tr>
            <tr><td>Jumlah Troly/Pack</td>@foreach ($details as $d)<td>{{ $d->trolley_count ?? '-' }}</td>@endforeach</tr>
            <tr><td>Suhu Produk</td>@foreach ($details as $d)<td>{{ $d->product_temp ?? '-' }}</td>@endforeach</tr>

            {{-- Step 1-7 --}}
            @foreach ($standardSteps as $order => $name)
                <tr class="bg-light">
                    <td><strong>{{ $order }}. {{ $name }}</strong></td>
                    @foreach ($details as $d)<td></td>@endforeach
                </tr>
                @foreach ($stepFields as $label => $field)
                    <tr>
                        <td>{{ $label }}</td>
                        @foreach ($details as $d)
                            @php $step = $d->steps->firstWhere('step_order', $order); @endphp
                            <td>{{ $step?->standardStep?->{$field} ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach

            {{-- Step 8: Drainage --}}
            <tr class="bg-light">
                <td><strong>8. Drainage Pressure</strong></td>
                @foreach ($details as $d)<td></td>@endforeach
            </tr>
            @foreach (['Jam Mulai (menit)' => 'start_time', 'Jam Selesai (menit)' => 'end_time'] as $label => $field)
                <tr>
                    <td>{{ $label }}</td>
                    @foreach ($details as $d)
                        @php $drainage = $d->steps->firstWhere('step_order', 8); @endphp
                        <td>{{ $drainage?->drainageStep?->{$field} ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Step 9: Finish --}}
            <tr class="bg-light">
                <td><strong>9. Finish Produk</strong></td>
                @foreach ($details as $d)<td></td>@endforeach
            </tr>
            @foreach (['Suhu Pusat Produk' => 'product_core_temp', 'Sortasi' => 'sortation'] as $label => $field)
                <tr>
                    <td>{{ $label }}</td>
                    @foreach ($details as $d)
                        @php $finish = $d->steps->firstWhere('step_order', 9); @endphp
                        <td>{{ $finish?->finishStep?->{$field} ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Paraf --}}
            <tr class="bg-light">
                <td><strong>Paraf</strong></td>
                @foreach ($details as $d)<td></td>@endforeach
            </tr>
            @foreach (['QC' => 'qc_paraf', 'Produksi' => 'production_paraf'] as $label => $field)
                <tr>
                    <td>{{ $label }}</td>
                    @foreach ($details as $d)
                        <td>
                            @if ($d->{$field})
                                <img src="{{ asset('storage/' . $d->{$field}) }}" alt="{{ $label }}" width="60">
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif