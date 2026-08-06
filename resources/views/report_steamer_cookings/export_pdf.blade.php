<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Proses Pemasakan di Steamer</title>
    <style>
    @font-face {
        font-family: "DejaVu Sans";
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format("truetype");
    }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 10px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
        background-color: #f2f2f2;
    }

    .text-center {
        text-align: center;
    }

    .no-border {
        border: none !important;
    }

    .fw-bold {
        font-weight: bold;
    }

    .mb-1 {
        margin-bottom: 4px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-3 {
        margin-bottom: 12px;
    }

    .info-table td {
        border: none;
        padding: 1px 0;
    }

    .batch-block {
        margin-bottom: 16px;
    }

    .header {
        position: fixed;
        top: -60px;
        left: 0;
        width: 100%;
        border: none;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    @page {
        margin-top: 80px;
    }
    </style>
</head>

@php
function isOutOfRange($value, $min, $max)
{
    if ($value === null || trim((string) $value) === '' || $min === null || $max === null) {
        return false;
    }
    if (!is_numeric($value) || !is_numeric($min) || !is_numeric($max)) {
        return false;
    }
    return ((float) $value < (float) $min) || ((float) $value > (float) $max);
}

function rangeStyle($value, $min, $max)
{
    return isOutOfRange($value, $min, $max) ? 'color:#c00; font-weight:bold;' : '';
}
@endphp

<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 60%; vertical-align: middle;">
                    <table style="border: none; border-collapse: collapse;">
                        <tr>
                            <td class="no-border" style="width: 30%; vertical-align: middle;">
                    <table style="border: none; border-collapse: collapse;">
                        <tr>
                            <td class="no-border" style="vertical-align: middle; width: 50px;">
                                @php
                                    $path = public_path('storage/image/logo.png');
                                    if (file_exists($path)) {
                                        $type   = pathinfo($path, PATHINFO_EXTENSION);
                                        $data   = file_get_contents($path);
                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                    }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="vertical-align: middle; padding-left: 8px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    PT. CHAROEN POKPHAND INDONESIA<br>
                                    FOOD DIVISION<br>
                                    {{ strtoupper($report->area->name ?? '') }} - INDONESIA
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border text-end"
                    style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <h3 class="text-center mb-2">VERIFIKASI PROSES PEMASAKAN DI STEAMER</h3>

    {{-- ===== A. INFORMASI PRODUK ===== --}}
    <div class="fw-bold mb-1">A. Informasi Produk</div>
    <table class="info-table mb-3" style="width: 70%;">
        <tr>
            <td width="160">Hari, Tanggal</td>
            <td width="15">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>:</td>
            <td>{{ $report->shift }}</td>
        </tr>
        <tr>
            <td>Nama Produk</td>
            <td>:</td>
            <td>{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kode Produk</td>
            <td>:</td>
            <td>{{ $report->product_code_range ?? '-' }}</td>
        </tr>
        <tr>
            <td>Gramasi</td>
            <td>:</td>
            <td>{{ $report->gramase }} gr</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI COOKING (per batch) ===== --}}
    <div class="fw-bold mb-1">B. Hasil Verifikasi Cooking</div>

    @foreach ($report->batches as $batch)
    @php $maxTemp = max(1, $batch->details->max(fn($d) => $d->coreTemps->count()) ?: 1); @endphp
    <div class="batch-block">
        <table class="info-table mb-2" style="width: 70%;">
            <tr>
                <td width="160">Nomor Steamer</td>
                <td width="15">:</td>
                <td>{{ $batch->steamer_number }}</td>
            </tr>
            <tr>
                <td>Jumlah Trolly</td>
                <td>:</td>
                <td>{{ $batch->trolley_count ?? '-' }} trolly</td>
            </tr>
            <tr>
                <td>Jumlah Tray/Trolly</td>
                <td>:</td>
                <td>{{ $batch->tray_per_trolley ?? '-' }} tray/trolly</td>
            </tr>
            <tr>
                <td>Waktu Proses</td>
                <td>:</td>
                <td>{{ $batch->start_time ?? '-' }} - {{ $batch->end_time ?? '-' }}</td>
            </tr>
        </table>

        <table class="text-center">
            <tr>
                <th rowspan="2">Kode<br>Produksi</th>
                <th rowspan="2">Start<br>Process</th>
                <th rowspan="2">End<br>Process</th>
                <th rowspan="2">
                    Setup Time <br>
                    <small>(std: {{ $standard->setup_time_min ?? null }} - {{ $standard->setup_time_max ?? null }})</small>
                </th>
                <th rowspan="2">
                    Suhu Ruang(&#176;C) <br>
                    <small>(std: {{$standard->room_temp_min ?? null }} - {{  $standard->room_temp_max ?? null }})</small>
                </th>
                <th colspan="{{ $maxTemp }}">
                    Actual Core Temp (&#176;C) <br>
                    <small>(std: {{$standard->core_temp_min ?? null }} - {{  $standard->core_temp_max ?? null }} )</small>
                </th>
                <th rowspan="2">Bentuk</th>
                <th rowspan="2">Warna</th>
                <th rowspan="2">Aroma</th>
                <th rowspan="2">Rasa</th>
                <th rowspan="2">Tekstur</th>
            </tr>
            <tr>
                @for ($i = 1; $i <= $maxTemp; $i++)
                <th>{{ $i }}</th>
                @endfor
            </tr>
            @forelse ($batch->details as $detail)
            <tr>
                <td>{{ $detail->production_code ?? '-' }}</td>
                <td>{{ $detail->start_process ?? '-' }}</td>
                <td>{{ $detail->end_process ?? '-' }}</td>
                <td style="{{ rangeStyle($detail->setup_time, $standard->setup_time_min ?? null, $standard->setup_time_max ?? null) }}">{{ $detail->setup_time ?? '-' }}</td>
                <td style="{{ rangeStyle($detail->room_temp, $standard->room_temp_min ?? null, $standard->room_temp_max ?? null) }}">{{ $detail->room_temp ?? '-' }}</td>
                @for ($i = 0; $i < $maxTemp; $i++)
                <td style="{{ rangeStyle(optional($detail->coreTemps[$i] ?? null)->temp_value, $standard->core_temp_min ?? null, $standard->core_temp_max ?? null) }}">{{ $detail->coreTemps[$i]->temp_value ?? '-' }}</td>
                @endfor
                <td>{{ $detail->sensory_bentuk ?? '-' }}</td>
                <td>{{ $detail->sensory_warna ?? '-' }}</td>
                <td>{{ $detail->sensory_aroma ?? '-' }}</td>
                <td>{{ $detail->sensory_rasa ?? '-' }}</td>
                <td>{{ $detail->sensory_tekstur ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 5 + $maxTemp + 5 }}">Belum ada data</td>
            </tr>
            @endforelse
        </table>
    </div>
    @endforeach

    {{-- ===== D. CATATAN & DOKUMENTASI ===== --}}
    <div class="fw-bold mb-1">D. Catatan & Dokumentasi</div>
    <p class="mb-3">
        {{ $report->notes ?: '-' }}
        @if ($report->curve_url)
        <br>Kurva pemasakan dapat dilihat pada {{ $report->curve_url }}
        @endif
    </p>

    {{-- ===== TANDA TANGAN ===== --}}
    <table style="width: 100%; border: none; margin-top: 2rem;">
        <tr style="border: none;">
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="70" style="margin: 6px 0;"><br>
                <strong>{{ $report->creator->name ?? $report->created_by ?? '-' }}</strong><br><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh:<br><br>
                @if($report->known_by)
                <img src="{{ $knownQr }}" width="70" style="margin: 6px 0;"><br>
                <strong>{{ $report->known_by }}</strong><br><br>
                @else
                <div style="height: 90px;"></div>
                <strong>-</strong><br>
                @endif
                Foreman / SPV Produksi
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="70" style="margin: 6px 0;"><br>
                <strong>{{ $report->approved_by }}</strong><br><br>
                @else
                <div style="height: 90px;"></div>
                <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

</body>

</html>