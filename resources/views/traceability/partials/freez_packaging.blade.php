@php
    $details->loadMissing('freezing.actualTemps');

    $time = fn($t) => $t ? \Carbon\Carbon::parse($t)->format('H:i') : '-';

    $docs = function ($collection) {
        if ($collection->isEmpty()) {
            return '-';
        }
        $html = '<div class="d-flex flex-wrap justify-content-center">';
        foreach ($collection as $doc) {
            $url = e(asset('storage/' . $doc->image));
            $html .= '<a href="' . $url . '" target="_blank" class="m-1"><img src="' . $url . '" alt="Dokumentasi" '
                . 'style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;border-radius:4px;"></a>';
        }
        return $html . '</div>';
    };
@endphp

<div class="table-responsive p-2 border rounded">
    <table class="table table-bordered text-center table-sm align-middle mb-0" style="font-size: 13px;">
        <thead>
            <tr>
                <th rowspan="2">Nama Produk</th>
                <th rowspan="2">Gramase</th>
                <th rowspan="2">Kode Produksi</th>
                <th rowspan="2">Best Before</th>
                <th rowspan="2">Release or Hold</th>
                <th rowspan="2">Tindakan Perbaikan</th>
                <th rowspan="2">Catatan Status Produk</th>
                <th rowspan="2">Waktu Proses</th>

                <th colspan="6">PEMBEKUAN</th>
                <th colspan="14">KARTONING</th>
            </tr>
            <tr>
                <th>Mesin</th>
                <th>Suhu Aktual Produk</th>
                <th>Standar Suhu</th>
                <th>Suhu Room IQF/ABF</th>
                <th>Dokumentasi</th>
                <th>Catatan Pembekuan</th>

                <th>Kondisi Karton</th>
                <th>Kondisi Label</th>
                <th>Isi per Kemasan Sekunder</th>
                <th>Isi per binded *prod. binded</th>
                <th>Isi per Inner *RTG</th>
                <th>Berat Standar (kg)</th>
                <th>Berat Karton 1</th>
                <th>Berat Karton 2</th>
                <th>Berat Karton 3</th>
                <th>Berat Karton 4</th>
                <th>Berat Karton 5</th>
                <th>Rata-Rata Berat</th>
                <th>Dokumentasi</th>
                <th>Catatan Pengemasan Sekunder</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $detail)
                @php
                    $fz = $detail->freezing;
                    $kt = $detail->kartoning;
                @endphp
                <tr>
                    <td class="align-middle">{{ $detail->product->product_name ?? '-' }}</td>
                    <td class="align-middle">
                        {{ !empty($detail->gramase) ? $detail->gramase : ($detail->product->nett_weight ?? '-') }} g
                    </td>
                    <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>
                    <td class="align-middle">{{ $detail->best_before ?? '-' }}</td>
                    <td class="align-middle">{{ $detail->release_status ?? '-' }}</td>
                    <td class="align-middle">{{ $detail->corrective_action ?? '-' }}</td>
                    <td class="align-middle">{{ $detail->notes ?? '-' }}</td>
                    <td class="align-middle">{{ $time($detail->start_time) }} - {{ $time($detail->end_time) }}</td>

                    {{-- Pembekuan --}}
                    <td class="align-middle">{{ $fz->iqf_machine ?? '-' }} ({{ $fz->machine_type ?? '-' }})</td>
                    <td class="align-middle">
                        @if ($fz && $fz->actualTemps->count())
                            @foreach ($fz->actualTemps as $temp)
                                {{ number_format($temp->actual_temp, 2) }}°C
                                @if (!$loop->last)<br>@endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="align-middle">{{ $fz->standard_temp ?? '-' }}</td>
                    <td class="align-middle">{{ $fz->iqf_room_temp ?? '-' }}</td>
                    <td class="align-middle">{!! $docs($detail->documentations) !!}</td>
                    <td class="align-middle">{{ $fz->notes ?? '-' }}</td>

                    {{-- Kartoning --}}
                    <td class="align-middle">{{ $kt->carton_condition ?? '-' }}</td>
                    <td class="align-middle">{{ $kt->label_condition ?? '-' }}</td>
                    <td class="align-middle">{{ $kt->content_bag ?? '-' }}</td>
                    <td class="align-middle">{{ $kt->content_binded ?? '-' }}</td>
                    <td class="align-middle">{{ $kt->content_rtg ?? '-' }}</td>
                    <td class="align-middle">{{ $kt->carton_weight_standard ?? '-' }}</td>
                    @for ($n = 1; $n <= 5; $n++)
                        <td class="align-middle">{{ $kt?->{'weight_' . $n} ?? '-' }}</td>
                    @endfor
                    <td class="align-middle">{{ $kt->avg_weight ?? '-' }}</td>
                    <td class="align-middle">{!! $docs($detail->kartoningDocumentations) !!}</td>
                    <td class="align-middle">{{ $kt->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="22" class="text-center">Tidak ada detail</td></tr>
            @endforelse
        </tbody>
    </table>
</div>