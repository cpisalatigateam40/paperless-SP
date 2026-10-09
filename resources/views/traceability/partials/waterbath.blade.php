@php
    $report->loadMissing(['details.product', 'pasteurisasi', 'coolingShocks', 'drippings']);

    $allDetails = $report->details->values();
    $pasteur    = $report->pasteurisasi->values();
    $cooling    = $report->coolingShocks->values();
    $drippings  = $report->drippings->values();

    $matchedIds = $details->map->getKey()->all();

    $pasteurFields = [
        'Suhu Awal Produk'                          => 'initial_product_temp',
        'Suhu Awal Air'                             => 'initial_water_temp',
        'Start Pasteurisasi'                        => 'start_time_pasteur',
        'Stop Pasteurisasi'                         => 'stop_time_pasteur',
        'Suhu air setelah produk dimasukkan panel'  => 'water_temp_after_input_panel',
        'Suhu air setelah produk dimasukkan aktual' => 'water_temp_after_input_actual',
        'Suhu air setting'                          => 'water_temp_setting',
        'Suhu air aktual'                           => 'water_temp_actual',
        'Suhu akhir air'                            => 'water_temp_final',
        'Suhu akhir produk'                         => 'product_temp_final',
    ];

    $coolingFields = [
        'Suhu Awal Air'      => 'initial_water_temp',
        'Start Pasteurisasi' => 'start_time_pasteur',
        'Stop Pasteurisasi'  => 'stop_time_pasteur',
        'Suhu air setting'   => 'water_temp_setting',
        'Suhu air aktual'    => 'water_temp_actual',
        'Suhu akhir air'     => 'water_temp_final',
        'Suhu akhir produk'  => 'product_temp_final',
    ];

    $drippingFields = [
        'Start Pasteurisasi' => 'start_time_pasteur',
        'Stop Pasteurisasi'  => 'stop_time_pasteur',
        'Suhu Zona Panas'    => 'hot_zone_temperature',
        'Suhu Zona Dingin'   => 'cold_zone_temperature',
        'Suhu Akhir Produk'  => 'product_temp_final',
    ];
@endphp

<div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
        <thead class="thead-light">
            <tr>
                <th>Produk</th>
                <th>Gramase</th>
                <th>Batch</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Pasteurisasi</th>
                <th>Cooling Shock</th>
                <th>Dripping</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $shown = 0; @endphp
            @foreach ($allDetails as $i => $d)
                @continue(!in_array($d->getKey(), $matchedIds))
                @php $shown++; @endphp
                <tr>
                    <td>{{ $d->product->product_name ?? '-' }}</td>
                    <td>{{ !empty($d->gramase) ? $d->gramase : ($d->product->nett_weight ?? '-') }} g</td>
                    <td>{{ $d->batch_code ?? '-' }}</td>
                    <td>{{ $d->amount ?? '-' }}</td>
                    <td>{{ $d->unit ?? '-' }}</td>

                    @foreach ([[$pasteur, $pasteurFields], [$cooling, $coolingFields], [$drippings, $drippingFields]] as [$list, $fields])
                        <td>
                            @if (isset($list[$i]))
                                @foreach ($fields as $label => $col)
                                    {{ $label }}: {{ $list[$i]->{$col} }}<br>
                                @endforeach
                            @endif
                        </td>
                    @endforeach

                    <td>{{ $d->note ?? '-' }}</td>
                </tr>
            @endforeach

            @if ($shown === 0)
                <tr><td colspan="9" class="text-center">Tidak ada detail</td></tr>
            @endif
        </tbody>
    </table>
</div>