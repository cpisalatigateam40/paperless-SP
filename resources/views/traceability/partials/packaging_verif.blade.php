<div class="table-responsive">
    <table class="table table-bordered table-sm text-center align-middle mb-0">
        <tr>
            <th rowspan="2">Jam</th>
            <th rowspan="2">Produk</th>
            <th rowspan="2">Gramase</th>
            <th rowspan="2">Kode Produksi</th>
            <th rowspan="2">Upload MD BPOM, QR Code, Kode Produksi, dan Expire Date</th>
            <th colspan="2">In cutting</th>
            <th colspan="2">Proses Pengemasan</th>
            <th colspan="2">Sampling Kemasan</th>
            <th colspan="2">Hasil Sealing</th>
            <th rowspan="2" class="text-nowrap">Isi Per-Pack</th>
            <th colspan="3">Panjang Produk Per Pcs</th>
            <th colspan="3">Berat Produk Per Pcs</th>
            <th colspan="3">Berat Produk Per Pack (gr)</th>
            <th rowspan="2">Keterangan</th>
        </tr>
        <tr>
            <th>Manual</th>
            <th>Mesin</th>
            <th>Thermoformer</th>
            <th>Manual</th>
            <th>Jumlah Sampling</th>
            <th>Hasil Sampling</th>
            <th>Kondisi Seal</th>
            <th>Vacum</th>
            <th>Standar</th><th>Aktual</th><th>Rata-Rata</th>
            <th>Standar</th><th>Aktual</th><th>Rata-Rata</th>
            <th>Standar</th><th>Aktual</th><th>Rata-Rata</th>
        </tr>

        @forelse ($details as $d)
            @php
                $checklist = $d->checklist;

                $contentPerPack = is_array($checklist?->content_per_pack_json)
                    ? $checklist->content_per_pack_json
                    : json_decode($checklist?->content_per_pack_json ?? '[]', true);

                if (empty($contentPerPack)) {
                    $contentPerPack = collect(range(1, 5))
                        ->map(fn($n) => $checklist?->{'content_per_pack_' . $n})
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->values()
                        ->toArray();
                }

                $files = array_filter(
                    json_decode($d->upload_md_multi ?? '[]', true) ?? [],
                    fn($file) => !empty($file)
                );
            @endphp

            @for ($i = 1; $i <= 5; $i++)
                <tr>
                    @if ($i == 1)
                        <td rowspan="5">{{ $d->time ? \Carbon\Carbon::parse($d->time)->format('H:i') : '-' }}</td>
                        <td rowspan="5">{{ $d->product->product_name ?? '-' }}</td>
                        <td rowspan="5">{{ !empty($d->gramase) ? $d->gramase : ($d->product->nett_weight ?? '-') }} g</td>
                        <td rowspan="5">{{ $d->production_code ?? '-' }}</td>
                        <td rowspan="5">
                            @foreach ($files as $file)
                                <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $file) }}" alt="Bukti" width="60"
                                        style="margin:4px; cursor:pointer;">
                                </a>
                            @endforeach
                        </td>

                        <td rowspan="5">{{ $checklist?->in_cutting_manual_1 ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->in_cutting_machine_1 ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->packaging_thermoformer_1 ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->packaging_manual_1 ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->sampling_amount ?? '-' }} {{ $checklist?->unit ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->sampling_result ?? '-' }}</td>
                    @endif

                    <td>{{ $checklist?->{'sealing_condition_' . $i} ?? '-' }}</td>
                    <td>{{ $checklist?->{'sealing_vacuum_' . $i} ?? '-' }}</td>
                    <td>
                        @forelse ($contentPerPack as $idx => $val)
                            <div><small>Pack {{ $idx + 1 }}:</small> {{ $val ?? '-' }}</div>
                        @empty
                            -
                        @endforelse
                    </td>

                    @if ($i == 1)
                        <td rowspan="5">{{ $checklist?->standard_long_pcs ?? '-' }}</td>
                    @endif
                    <td>{{ $checklist?->{'actual_long_pcs_' . $i} ?? '-' }}</td>
                    @if ($i == 1)
                        <td rowspan="5">{{ $checklist?->avg_long_pcs ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->standard_weight_pcs ?? '-' }}</td>
                    @endif
                    <td>{{ $checklist?->{'actual_weight_pcs_' . $i} ?? '-' }}</td>
                    @if ($i == 1)
                        <td rowspan="5">{{ $checklist?->avg_weight_pcs ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->standard_weight ?? '-' }}</td>
                    @endif
                    <td>{{ $checklist?->{'actual_weight_' . $i} ?? '-' }}</td>
                    @if ($i == 1)
                        <td rowspan="5">{{ $checklist?->avg_weight ?? '-' }}</td>
                        <td rowspan="5">{{ $checklist?->notes ?? '-' }}</td>
                    @endif
                </tr>
            @endfor
        @empty
            <tr><td colspan="23">Tidak ada detail</td></tr>
        @endforelse
    </table>
</div>