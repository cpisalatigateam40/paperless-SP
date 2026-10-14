@php
    $details->loadMissing('item.section');

    $criteriaPairs = [[1, 2], [3, 4], [5, 6], [7, 8]];

    $groupLabels = [
        'sisa_bahan'      => 'Sisa Bahan dan Kemasan',
        'mesin_peralatan' => 'Mesin dan Peralatan',
        'kondisi_ruangan' => 'Kondisi Ruangan',
    ];

    $batches = $details->groupBy(fn($d) => $d->product_uuid . '|' . $d->time)->map(function ($rows) use ($groupLabels) {
        $first = $rows->first();

        $batch = [
            'product_name'    => $first->product->product_name ?? '-',
            'time'            => $first->time ? \Illuminate\Support\Str::substr($first->time, 0, 5) : '-',
            'production_code' => $rows->pluck('production_code')->filter()->unique()->implode(', ') ?: '-',
            'section_names'   => $rows->where('group', 'mesin_peralatan')
                                    ->pluck('item.section.section_name')
                                    ->filter()->unique()->implode(', ') ?: '-',
        ];

        foreach (array_keys($groupLabels) as $g) {
            $batch[$g] = $rows->where('group', $g)->values()->map(fn($d) => [
                'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                'score'             => $d->score,
                'notes'             => $d->notes,
                'corrective_action' => $d->corrective_action,
            ])->all();
        }

        return $batch;
    });
@endphp

@forelse ($batches as $batch)
    <div class="border rounded p-2 mb-3">
        <table class="table table-sm table-borderless mb-2" style="width: auto;">
            <tr><td class="font-weight-bold" style="width:140px;">Produk</td><td>: {{ $batch['product_name'] }}</td></tr>
            <tr><td class="font-weight-bold">Kode Produksi</td><td>: {{ $batch['production_code'] }}</td></tr>
            <tr><td class="font-weight-bold">Jam</td><td>: {{ $batch['time'] }}</td></tr>
            <tr><td class="font-weight-bold">Section</td><td>: {{ $batch['section_names'] }}</td></tr>
        </table>

        @foreach ($groupLabels as $groupKey => $groupLabel)
            <h6 class="font-weight-bold mt-2">{{ $groupLabel }}</h6>
            <table class="table table-sm table-bordered mb-2">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:280px;">Item</th>
                        @foreach ($criteriaPairs as $pair)
                            <th class="text-center" style="width:60px;">{{ implode('/', $pair) }}</th>
                        @endforeach
                        <th style="width:400px;">Tindakan Koreksi</th>
                        <th style="width:400px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batch[$groupKey] as $i => $row)
                        @php $rowScores = array_map('strval', (array) ($row['score'] ?? [])); @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-left">{{ $row['name'] }}</td>
                            @foreach ($criteriaPairs as $pair)
                                @php $matched = collect($pair)->first(fn($num) => in_array((string) $num, $rowScores)); @endphp
                                <td class="text-center">{{ $matched ?? '' }}</td>
                            @endforeach
                            <td class="text-left">{{ $row['corrective_action'] ?? '-' }}</td>
                            <td class="text-left">{{ $row['notes'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 4 + count($criteriaPairs) }}">Belum ada data {{ strtolower($groupLabel) }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    </div>
@empty
    <p class="text-muted mb-0">Belum ada detail</p>
@endforelse