<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Kinerja Metal Detector Produk</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 12px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 2px 3px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .signature-box {
        height: 40px;
        border-bottom: 1px solid #000;
        margin-top: 20px;
        width: 60%;
    }

    .no-border {
        border: none !important;
    }

    .mb-2 {
        margin-bottom: 1rem;
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

    .mb-4 {
        margin-bottom: 2rem;
    }

    .underline {
        text-decoration: underline;
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

    .info-table td {
        border: none;
        padding: 1px 3px;
    }

    .info-label {
        width: 110px;
    }

    @page {
        margin-top: 80px;
        size: 210mm 330mm;
    }
    </style>
</head>

<body>
    {{-- header --}}
    <div class="header">
        <table class="header-table">
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
                <td class="no-border" style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <h3 class="mb-2 text-center" style="text-transform: uppercase;">Verifikasi Kinerja Metal Detector Produk</h3>

    {{-- A. INFORMASI PRODUK --}}
    <strong>A. Informasi Produk</strong>
    <table class="info-table">
        <tr>
            <td class="info-label">Hari, Tanggal</td>
            <td style="width: 15px;">:</td>
            <td class="underline">{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Shift</td>
            <td>:</td>
            <td class="underline">{{ $report->shift }}</td>
        </tr>
        <tr>
            <td class="info-label">Area</td>
            <td>:</td>
            <td class="underline">{{ $report->area->name ?? '-' }} (Packing)</td>
        </tr>
    </table>

    {{-- B. HASIL VERIFIKASI --}}
    <strong>B. Hasil Verifikasi</strong>
    <table class="info-table">
        <tr>
            <td class="info-label">Merk</td>
            <td style="width: 15px;">:</td>
            <td class="underline">{{ $report->metalDetector->merk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Type/model</td>
            <td>:</td>
            <td class="underline">{{ $report->metalDetector->type_model ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">No. Series</td>
            <td>:</td>
            <td class="underline">{{ $report->metalDetector->no_series ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Waktu<br>Verifikasi</th>
                <th rowspan="2">Nama Produk</th>
                <th rowspan="2">Gramase<br>(gr)</th>
                <th rowspan="2">Kode Produksi</th>
                <th colspan="3">Fe 1.5 mm</th>
                <th colspan="3">Non-Fe 2.0 mm</th>
                <th colspan="3">SUS 2.5 mm</th>
                <th rowspan="2">Status<br>(OK/NG)</th>
                <th rowspan="2">Tindakan<br>Koreksi</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>D</th>
                <th>T</th>
                <th>B</th>
                <th>D</th>
                <th>T</th>
                <th>B</th>
                <th>D</th>
                <th>T</th>
                <th>B</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</td>
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                <td class="text-center">{{ !empty($detail->gramase)
                                                        ? $detail->gramase
                                                        : ($detail->product->nett_weight ?? '-') }}</td>
                <td>{{ $detail->production_code ?? '-' }}</td>
                @php
                $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
                $positions = ['d', 't', 'b'];
                @endphp
                @foreach ($specimens as $specimen)
                @foreach ($positions as $pos)
                @php
                $posDetail = $detail->positions->where('specimen', $specimen)->where('position', $pos)->first();
                @endphp
                <td class="text-center">{{ $posDetail ? ($posDetail->status ? 'OK' : 'NG') : '-' }}</td>
                @endforeach
                @endforeach
                <td class="text-center">{{ $detail->status ? 'OK' : 'NG' }}</td>
                <td>{{ $detail->corrective_action ?: '-' }}</td>
                <td>{{ $detail->verification ?: '-' }}</td>
            </tr>
            @endforeach
            <!-- <tr>
                <td colspan="17" style="text-align: right; border: none;">{{ $formNumber ?? '-' }}</td>
            </tr> -->
        </tbody>
    </table>

    <p>D = depan ; T = tengah ; B = belakang</p>

    {{-- C. CATATAN & DOKUMENTASI --}}
    <strong>C. Catatan & Dokumentasi</strong>
    <table class="info-table">
        <tr>
            <td>{{ $report->notes ?? '-' }}</td>
        </tr>
    </table>

    <table style="width: 100%; border: none; margin-top: 2rem;">
        <tr style="border: none;">
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="80" style="margin: 10px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh:<br><br>
                @if($report->known_by)
                <img src="{{ $knownQr }}" width="80" style="margin: 10px 0;"><br>
                <strong>{{ $report->known_by }}</strong><br><br>
                @else
                <div style="height: 120px;"></div>
                <strong>-</strong><br>
                @endif
                SPV/Foreman/Lady Produksi
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="80" style="margin: 10px 0;"><br>
                <strong>{{ $report->approved_by }}</strong><br><br>
                @else
                <div style="height: 120px;"></div>
                <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>
</body>

</html>