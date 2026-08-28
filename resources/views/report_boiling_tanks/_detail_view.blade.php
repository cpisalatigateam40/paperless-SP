@php
    $maxChecks = max($report->details->max(fn ($d) => $d->checks->count()) ?? 0, 3);

    $standard = \App\Models\MasterBoilingTankStandard::where('area_uuid', $report->area_uuid)
        ->where('product_uuid', $report->product_uuid)
        ->first();
@endphp

<div class="mb-3">
    <div class="fw-bold mb-1">A. Informasi Produk</div>
    <table class="table table-borderless table-sm mb-0" style="width: auto;">
        <tr>
            <td class="text-muted" style="width: 140px;">Hari, Tanggal</td>
            <td>: {{ optional($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="text-muted">Shift</td>
            <td>: {{ $report->shift }}</td>
        </tr>
        <tr>
            <td class="text-muted">Nama Produk</td>
            <td>: {{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-muted">Kode Produk</td>
            <td>: {{ $report->product_code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-muted">Gramasi</td>
            <td>: {{ $report->gramasi ?? '-' }} gr</td>
        </tr>
    </table>
</div>

<div class="mb-2">
    <div class="fw-bold mb-1">B. Hasil Verifikasi Cooking</div>
    <table class="table table-borderless table-sm mb-2" style="width: auto;">
        <tr>
            <td class="text-muted" style="width: 140px;">Line Boiling Tank</td>
            <td>: {{ $report->line_boiling_tank ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-muted">Waktu Proses</td>
            <td>: {{ $report->waktu_proses_start ?? '--:--' }} - {{ $report->waktu_proses_end ?? '--:--' }}</td>
        </tr>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm text-center align-middle bg-white mb-0">
        <thead>
            <tr>
                <th rowspan="2">Kode Produksi</th>
                <th rowspan="2">Start</th>
                <th rowspan="2">End</th>
                <th rowspan="2">Suhu Adonan (°C)<br><small class="text-muted">Std 16 ± 2°C</small></th>
                <th rowspan="2">Aktual Suhu Air Tangki I (°C)<br><small class="text-muted">{{ $standard?->suhu_tangki_1_label ? 'Std ' . $standard->suhu_tangki_1_label . '°C' : '-' }}</small></th>
                <th rowspan="2">Aktual Suhu Air Tangki II (°C)<br><small class="text-muted">{{ $standard?->suhu_tangki_2_label ? 'Std ' . $standard->suhu_tangki_2_label . '°C' : '-' }}</small></th>
                <th colspan="{{ $maxChecks }}">Berat Mentah (gr)<br><small class="text-muted">{{ $standard?->berat_mentah_label ? 'Std ' . $standard->berat_mentah_label . ' gr' : '-' }}</small></th>
                <th colspan="{{ $maxChecks }}">Actual Core Temp (°C)<br><small class="text-muted">{{ $standard?->actual_core_temp_label ? 'Std ' . $standard->actual_core_temp_label . '°C' : '-' }}</small></th>
                <th colspan="{{ $maxChecks }}">Berat Matang (gr)<br><small class="text-muted">{{ $standard?->berat_matang_label ? 'Std ' . $standard->berat_matang_label . ' gr' : '-' }}</small></th>
                <th colspan="{{ $maxChecks }}">Suhu After Cooling (°C)</th>
                <th rowspan="2">Bentuk</th>
                <th rowspan="2">Warna</th>
                <th rowspan="2">Aroma</th>
                <th rowspan="2">Rasa</th>
                <th rowspan="2">Tekstur</th>
            </tr>
            <tr>
                @for($i = 1; $i <= $maxChecks; $i++)
                    <th>{{ $i }}</th>
                @endfor
                @for($i = 1; $i <= $maxChecks; $i++)
                    <th>{{ $i }}</th>
                @endfor
                @for($i = 1; $i <= $maxChecks; $i++)
                    <th>{{ $i }}</th>
                @endfor
                @for($i = 1; $i <= $maxChecks; $i++)
                    <th>{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($report->details as $detail)
                <tr>
                    <td>{{ $detail->kode_produksi ?? '-' }}</td>
                    <td>{{ $detail->start ?? '-' }}</td>
                    <td>{{ $detail->end ?? '-' }}</td>
                    <td>{{ $detail->suhu_adonan ?? '-' }}</td>
                    <td>{{ $detail->aktual_suhu_tangki_1 ?? '-' }}</td>
                    <td>{{ $detail->aktual_suhu_tangki_2 ?? '-' }}</td>

                    @for($i = 0; $i < $maxChecks; $i++)
                        <td>{{ $detail->checks->get($i)?->berat_mentah ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td>{{ $detail->checks->get($i)?->actual_core_temp ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td>{{ $detail->checks->get($i)?->berat_matang ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td>{{ $detail->checks->get($i)?->suhu_after_cooling ?? '-' }}</td>
                    @endfor

                    <td>{{ $detail->sensori_bentuk ?? '-' }}</td>
                    <td>{{ $detail->sensori_warna ?? '-' }}</td>
                    <td>{{ $detail->sensori_aroma ?? '-' }}</td>
                    <td>{{ $detail->sensori_rasa ?? '-' }}</td>
                    <td>{{ $detail->sensori_tekstur ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 11 + ($maxChecks * 4) }}" class="text-muted">Belum ada Kode Produksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    <div class="fw-bold mb-1">D. Catatan & Dokumentasi</div>
    <div>
        Catatan: {{ $report->link_kurva }}
    </div>
</div>