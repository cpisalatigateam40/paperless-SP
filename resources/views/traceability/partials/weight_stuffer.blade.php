@php
    $labels = [
        'Kecepatan Stuffer (rpm)'                           => 'speed',
        'Ukuran Casing<br><small>(Aktual Diameter)</small>' => 'casing',
        'Standar Berat (gr)'                                => 'standard',
        'Berat Aktual (gr)'                                 => 'actual_weight',
        'Rata-rata Berat Aktual (gr)'                       => 'avg',
        'Status Berat'                                      => 'weight_status',
        'Tindakan Koreksi Berat'                            => 'weight_corrective_action',
        'Keterangan Berat'                                  => 'weight_notes',
        'Standar Panjang'                                   => 'standard_long',
        'Panjang Aktual'                                    => 'actual_long',
        'Rata-rata Panjang Aktual'                          => 'avg_long',
        'Status Panjang'                                    => 'long_status',
        'Tindakan Koreksi Panjang'                          => 'long_corrective_action',
        'Keterangan Panjang'                                => 'long_notes',
        'Standar Berat Fla'                                 => 'standard_fla',
        'Berat Aktual Fla'                                  => 'actual_fla',
        'Rata-rata Berat Aktual Fla'                        => 'avg_fla',
        'Status Berat Fla'                                  => 'fla_status',
        'Tindakan Koreksi Berat Fla'                        => 'fla_corrective_action',
        'Keterangan Berat Fla'                              => 'fla_notes',
        'Catatan'                                           => 'notes',
    ];

    // Nilai per detail dihitung sekali, bukan di dalam loop label
    $rows = $details->map(function ($d) {
        $stuffer = $d->townsend ?? $d->hitech ?? $d->vemag ?? $d->vemag2 ?? $d->handtmann;
        $case    = $d->cases->first();

        $machine = '-';
        if ($d->townsend)      $machine = 'Townsend';
        elseif ($d->hitech)    $machine = 'Hitech';
        elseif ($d->vemag)     $machine = 'Vemag';
        elseif ($d->vemag2)    $machine = 'Vemag 2';
        elseif ($d->handtmann) $machine = 'Handtmann';

        return [
            'd' => $d,
            'machine' => $machine,
            'time' => $d->time ? \Carbon\Carbon::parse($d->time)->format('H:i') : '-',
            'values' => [
                'speed'         => $stuffer?->stuffer_speed,
                'casing'        => $case?->actual_case_2,
                'standard'      => $d->weight_standard,
                'actual_weight' => $d->weights->pluck('actual_weight')->filter()->implode(' / ') ?: null,
                'avg'           => $stuffer?->avg_weight,
                'weight_status' => $d->weight_status,
                'weight_corrective_action' => $d->weight_corrective_action,
                'weight_notes'  => $d->weight_notes,
                'standard_long' => $d->long_standard,
                'actual_long'   => $d->weights->pluck('actual_long')->filter()->implode(' / ') ?: null,
                'avg_long'      => $stuffer?->avg_long,
                'long_status'   => $d->long_status,
                'long_corrective_action' => $d->long_corrective_action,
                'long_notes'    => $d->long_notes,
                'standard_fla'  => $d->fla_standard,
                'actual_fla'    => $d->weights->pluck('actual_fla')->filter()->implode(' / ') ?: null,
                'avg_fla'       => $stuffer?->avg_fla,
                'fla_status'    => $d->fla_status,
                'fla_corrective_action' => $d->fla_corrective_action,
                'fla_notes'     => $d->fla_notes,
                'notes'         => $stuffer?->notes,
            ],
        ];
    });
@endphp

<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm text-center align-middle mb-3">
        <tr>
            <th class="text-left">Nama Produk</th>
            @foreach ($rows as $r)
                <th>{{ $r['d']->product->product_name ?? '-' }}</th>
            @endforeach
        </tr>
        <tr>
            <th class="text-left">Gramase</th>
            @foreach ($rows as $r)
                <th>{{ !empty($r['d']->gramase) ? $r['d']->gramase : ($r['d']->product->nett_weight ?? '-') }} g</th>
            @endforeach
        </tr>
        <tr>
            <th class="text-left">Kode Produksi</th>
            @foreach ($rows as $r)
                <td>{{ $r['d']->production_code }}</td>
            @endforeach
        </tr>
        <tr>
            <th class="text-left">Waktu Proses</th>
            @foreach ($rows as $r)
                <td>{{ $r['time'] }}</td>
            @endforeach
        </tr>
        <tr>
            <th class="text-left">Mesin Stuffer</th>
            @foreach ($rows as $r)
                <td>{{ $r['machine'] }}</td>
            @endforeach
        </tr>

        @foreach ($labels as $label => $key)
            <tr>
                <td class="text-left">{!! $label !!}</td>
                @foreach ($rows as $r)
                    <td>{{ $r['values'][$key] ?? '-' }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    @foreach ($rows as $r)
        @if ($r['d']->documentations->isNotEmpty())
            <div class="mb-3">
                <p class="mb-1 font-weight-bold" style="font-size:13px">
                    Dokumentasi — {{ $r['d']->product->product_name ?? '-' }}
                    <span class="text-muted font-weight-normal">({{ $r['time'] }})</span>
                </p>
                <div class="d-flex flex-wrap">
                    @foreach ($r['d']->documentations as $doc)
                        <a href="{{ Storage::url($doc->image) }}" target="_blank" class="mr-2 mb-2">
                            <img src="{{ Storage::url($doc->image) }}"
                                style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;"
                                title="{{ $r['d']->product->product_name ?? '-' }}">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>