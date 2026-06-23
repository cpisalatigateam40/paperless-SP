<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Proses Stuffing</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        line-height: 1.25;
        margin: 20px;
    }
    h3 {
        text-align: center;
        margin: 2px 0 8px 0;
        font-size: 12px;
    }
    .section-title {
        font-weight: bold;
        margin-top: 1rem;
        margin-bottom: 4px;
        color: #1a56a0;
    }
    table.info {
        width: 100%;
        border-collapse: collapse;
    }
    table.info td {
        padding: 1px 2px;
        vertical-align: top;
        border: none;
    }
    table.data {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    table.data th, table.data td {
        border: 1px solid #000;
        padding: 2px 4px;
        text-align: center;
        vertical-align: middle;
    }
    table.data th {
        font-weight: bold;
        background-color: #f0f0f0;
    }
    table.data td.text-left {
        text-align: left;
    }
    .header {
        position: fixed;
        top: -60px;
        left: 0;
        width: 100%;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .no-border {
        border: none !important;
    }
    .page-break {
        page-break-after: always;
    }
    @page {
        margin-top: 75px;
        margin-bottom: 45px;
        margin-left: 35px;
        margin-right: 35px;
    }
    </style>
</head>
<body>

{{-- ===== HEADER FIXED ===== --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td class="no-border" style="width:30%; vertical-align:middle;">
                <table style="border:none; border-collapse:collapse;">
                    <tr>
                        <td class="no-border" style="width:50px; vertical-align:middle;">
                            @php
                                $path = public_path('storage/image/logo.png');
                                if (file_exists($path)) {
                                    $type   = pathinfo($path, PATHINFO_EXTENSION);
                                    $data   = file_get_contents($path);
                                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                            @endphp
                            <img src="{{ $base64 ?? '' }}" style="width:50px;">
                        </td>
                        <td class="no-border" style="padding-left:8px; vertical-align:middle;">
                            <div style="font-size:9px; font-weight:bold; line-height:1.2;">
                                PT. CHAROEN POKPHAND INDONESIA<br>
                                FOOD DIVISION<br>
                                SALATIGA - INDONESIA
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="no-border" style="text-align:right; vertical-align:middle; font-size:9px; font-weight:bold;">
                QM P.2 / 02
            </td>
        </tr>
    </table>
</div>

<h3>VERIFIKASI PROSES STUFFING</h3>

@php $first = $report->details->first(); @endphp

{{-- ===== A. INFORMASI PRODUK ===== --}}
<div class="section-title">A. Informasi Produk</div>
<table class="info" style="margin-bottom:10px;">
    <tr>
        <td width="130">Hari, Tanggal</td>
        <td width="10">:</td>
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
        <td>{{ $first?->product?->product_name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Kode Produk</td>
        <td>:</td>
        <td>{{ $first?->production_code ?? '-' }}</td>
    </tr>
    <tr>
        <td>Gramasi</td>
        <td>:</td>
        <td>{{ $first?->gramase ?? '-' }} gr</td>
    </tr>
</table>

{{-- ===== B. HASIL VERIFIKASI (per mesin) ===== --}}
<div class="section-title">B. Hasil Verifikasi</div>

@foreach($report->details as $detail)
@php
    $stuffer =
        $detail->townsend ??
        $detail->hitech   ??
        $detail->vemag    ??
        $detail->vemag2   ??
        $detail->handtmann;

    $machineLabel = match(true) {
        $detail->townsend  != null => 'Townsend',
        $detail->hitech    != null => 'Hitech',
        $detail->vemag     != null => 'Vemag',
        $detail->vemag2    != null => 'Vemag 2',
        $detail->handtmann != null => 'Handtmann',
        default                    => '-',
    };

    $weights = $detail->weights;
    $maxCols = max(3, $weights->count()); // minimal tampilkan 3 kolom
@endphp

<table class="info" style="margin-bottom:4px;">
    <tr>
        <td width="130">Mesin</td>
        <td width="10">:</td>
        <td>{{ $machineLabel }}</td>
    </tr>
    <tr>
        <td>Waktu Verifikasi</td>
        <td>:</td>
        <td>{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }} WIB</td>
    </tr>
    <tr>
        <td>Diameter casing</td>
        <td>:</td>
        <td>{{ $detail->cases->first()?->actual_case_2 ?? '-' }} mm</td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th rowspan="2" style="width:5%">No</th>
            <th rowspan="2" style="width:22%">Parameter Verifikasi</th>
            <th rowspan="2" style="width:15%">Standar</th>
            <th colspan="{{ $maxCols + 1 }}">Hasil Aktual</th>
            <th rowspan="2" style="width:10%">Rata-rata aktual</th>
            <th rowspan="2" style="width:10%">Status (OK/NG)</th>
            <th rowspan="2" style="width:14%">Tindakan Koreksi</th>
            <th rowspan="2" style="width:14%">Keterangan</th>
        </tr>
        <tr>
            @for($i = 1; $i <= $maxCols; $i++)
                <th style="width:5%">{{ $i }}</th>
            @endfor
            <th style="width:5%">Etc</th>
        </tr>
    </thead>
    <tbody>
        {{-- Baris 1: Berat per 3 pcs --}}
        <tr>
            <td>1</td>
            <td class="text-left">Berat per 3 pcs (gr)</td>
            <td>{{ $detail->weight_standard ?? '-' }}</td>
            @for($i = 0; $i < $maxCols; $i++)
                <td>{{ $weights->get($i)?->actual_weight ?? '-' }}</td>
            @endfor
            <td>{{ $weights->count() > $maxCols ? '(…)' : '-' }}</td>
            <td>{{ $stuffer?->avg_weight ?? '-' }}</td>
            <td>{{ $detail->weight_status ?? '-' }}</td>
            <td>{{ $detail->weight_corrective_action ?? '-' }}</td>
            <td>{{ $detail->weight_notes ?? '-' }}</td>
        </tr>

        {{-- Baris 2: Panjang per pcs --}}
        <tr>
            <td>2</td>
            <td class="text-left">Panjang per pcs (mm)</td>
            <td>{{ $detail->long_standard ?? '-' }}</td>
            @for($i = 0; $i < $maxCols; $i++)
                <td>{{ $weights->get($i)?->actual_long ?? '-' }}</td>
            @endfor
            <td>{{ $weights->count() > $maxCols ? '(…)' : '-' }}</td>
            <td>{{ $stuffer?->avg_long ?? '-' }}</td>
            <td>{{ $detail->long_status ?? '-' }}</td>
            <td>{{ $detail->long_corrective_action ?? '-' }}</td>
            <td>{{ $detail->long_notes ?? '-' }}</td>
        </tr>

        {{-- Baris 3: Berat Fla --}}
        <tr>
            <td>3</td>
            <td class="text-left">Berat fla (gr)</td>
            <td>{{ $detail->fla_standard ?? '-' }}</td>
            @for($i = 0; $i < $maxCols; $i++)
                <td>{{ $weights->get($i)?->actual_fla ?? '-' }}</td>
            @endfor
            <td>{{ $weights->count() > $maxCols ? '(…)' : '-' }}</td>
            <td>{{ $stuffer?->avg_fla ?? '-' }}</td>
            <td>{{ $detail->fla_status ?? '-' }}</td>
            <td>{{ $detail->fla_corrective_action ?? '-' }}</td>
            <td>{{ $detail->fla_notes ?? '-' }}</td>
        </tr>
    </tbody>
</table>

@endforeach

{{-- ===== C. CATATAN & DOKUMENTASI ===== --}}
<div class="section-title">C. Catatan & Dokumentasi</div>

@foreach($report->details as $detail)
@php
    $machine =
        $detail->townsend ??
        $detail->hitech ??
        $detail->vemag ??
        $detail->vemag2 ??
        $detail->handtmann;

    $machineLabel = match(true) {
        $detail->townsend  != null => 'Townsend',
        $detail->hitech    != null => 'Hitech',
        $detail->vemag     != null => 'Vemag',
        $detail->vemag2    != null => 'Vemag 2',
        $detail->handtmann != null => 'Handtmann',
        default => '-',
    };
@endphp

{{-- OUTER TABLE (INI KUNCI UTAMA SIDE BY SIDE) --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
    <tr>

        {{-- ================= LEFT SIDE (INFO) ================= --}}
        <td style="width:35%; vertical-align:top; padding-right:10px;">

            <table class="info" style="width:100%;">
                <tr>
                    <td width="80">Mesin</td>
                    <td width="10">:</td>
                    <td>{{ $machineLabel }}</td>
                </tr>
                <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ $machine?->notes ?? '-' }}</td>
                </tr>
            </table>

        </td>

        {{-- ================= RIGHT SIDE (IMAGES GRID) ================= --}}
        <td style="width:65%; vertical-align:top;">

            @if($detail->documentations && $detail->documentations->count())
                @php
                    $docs = $detail->documentations->values();
                    $chunked = $docs->chunk(2);
                @endphp

                <table style="width:100%; border-collapse:collapse;">
                    @foreach($chunked as $row)
                        <tr>
                            @foreach($row as $doc)
                                <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                                    @if(!empty($doc->image))
                                        @php
                                            $path = storage_path('app/public/' . $doc->image);

                                            $base64 = null;

                                            if (file_exists($path)) {
                                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                                $data = file_get_contents($path);
                                                $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);
                                            }
                                        @endphp

                                        @if($base64)
                                            <img src="{{ $base64 }}"
                                                 style="width:100%; max-width:160px; border:1px solid #ccc;">
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            {{-- kalau ganjil --}}
                            @if($row->count() < 2)
                                <td style="width:50%;"></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            @endif

        </td>
    </tr>
</table>

<hr>
@endforeach

{{-- ===== TTD ===== --}}
<table style="width:100%; border:none; margin-top:30px;">
    <tr>
        <td style="text-align:center; border:none; width:33%;">
            Diperiksa oleh,<br><br>
            <img src="{{ $createdQr }}" width="80" style="margin:8px 0;"><br>
            <strong>{{ $report->created_by }}</strong><br>
            QC Inspector
        </td>
        <td style="text-align:center; border:none; width:33%;">
            Diketahui oleh,<br><br>
            @if($report->known_by)
                <img src="{{ $knownQr }}" width="80" style="margin:8px 0;"><br>
                <strong>{{ $report->known_by }}</strong><br>
            @else
                <div style="height:100px;"></div>
                <strong>Tanda Tangan &amp; Nama Terang</strong><br>
            @endif
            Foreman / SPV Produksi
        </td>
        <td style="text-align:center; border:none; width:33%;">
            Disetujui oleh,<br><br>
            @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="80" style="margin:8px 0;"><br>
                <strong>{{ $report->approved_by }}</strong><br>
            @else
                <div style="height:100px;"></div>
                <strong>Tanda Tangan &amp; Nama Terang</strong><br>
            @endif
            Supervisor QC
        </td>
    </tr>
</table>

</body>
</html>