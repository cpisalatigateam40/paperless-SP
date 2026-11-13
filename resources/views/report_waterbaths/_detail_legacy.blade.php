@php
    $max = max(
        $report->details->count(),
        $report->pasteurisasi->count(),
        $report->coolingShocks->count(),
        $report->drippings->count()
    );
@endphp

<div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
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
            @for($i = 0; $i < $max; $i++)
            @php
                $d  = $report->details->get($i);
                $p  = $report->pasteurisasi->get($i);
                $c  = $report->coolingShocks->get($i);
                $dr = $report->drippings->get($i);
            @endphp
            <tr>
                {{-- Detail Produk --}}
                <td>{{ $d?->product->product_name ?? '-' }}</td>
                <td>{{ !empty($d?->gramase) ? $d->gramase : ($d?->product->nett_weight ?? '-') }} g</td>
                <td>{{ $d?->batch_code ?? '-' }}</td>
                <td>{{ $d?->amount ?? '-' }}</td>
                <td>{{ $d?->unit ?? '-' }}</td>

                {{-- Pasteurisasi --}}
                <td>
                    @if($p)
                        Suhu Awal Produk: {{ $p->initial_product_temp }}<br>
                        Suhu Awal Air: {{ $p->initial_water_temp }}<br>
                        Start Pasteurisasi: {{ $p->start_time_pasteur }}<br>
                        Stop Pasteurisasi: {{ $p->stop_time_pasteur }}<br>
                        Suhu air setelah produk dimasukkan panel: {{ $p->water_temp_after_input_panel }}<br>
                        Suhu air setelah produk dimasukkan aktual: {{ $p->water_temp_after_input_actual }}<br>
                        Suhu air setting: {{ $p->water_temp_setting }}<br>
                        Suhu air aktual: {{ $p->water_temp_actual }}<br>
                        Suhu akhir air: {{ $p->water_temp_final }}<br>
                        Suhu akhir produk: {{ $p->product_temp_final }}
                    @endif
                </td>

                {{-- Cooling Shock --}}
                <td>
                    @if($c)
                        Suhu Awal Air: {{ $c->initial_water_temp }}<br>
                        Start Pasteurisasi: {{ $c->start_time_pasteur }}<br>
                        Stop Pasteurisasi: {{ $c->stop_time_pasteur }}<br>
                        Suhu air setting: {{ $c->water_temp_setting }}<br>
                        Suhu air aktual: {{ $c->water_temp_actual }}<br>
                        Suhu akhir air: {{ $c->water_temp_final }}<br>
                        Suhu akhir produk: {{ $c->product_temp_final }}
                    @endif
                </td>

                {{-- Dripping --}}
                <td>
                    @if($dr)
                        Start Pasteurisasi: {{ $dr->start_time_pasteur }}<br>
                        Stop Pasteurisasi: {{ $dr->stop_time_pasteur }}<br>
                        Suhu Zona Panas: {{ $dr->hot_zone_temperature }}<br>
                        Suhu Zona Dingin: {{ $dr->cold_zone_temperature }}<br>
                        Suhu Akhir Produk: {{ $dr->product_temp_final }}
                    @endif
                </td>

                <td>{{ $d?->note ?? '-' }}</td>
            </tr>
            @endfor
        </tbody>
    </table>
</div>

@can('create report')
<div class="d-flex justify-content-end mt-2">
    <a href="{{ route('report_waterbaths.add_detail', $report->uuid) }}"
        class="btn btn-sm btn-secondary">
        + Tambah Detail
    </a>
</div>
@endcan