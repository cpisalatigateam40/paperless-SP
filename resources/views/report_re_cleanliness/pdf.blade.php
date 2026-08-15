<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 8px;
        line-height: 1.25;
        margin: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 8px;
        table-layout: fixed;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 1.5px 2px;
        text-align: left;
        vertical-align: middle;
        word-wrap: break-word;
        font-size: 7.5px;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    thead {
        display: table-header-group;
    }

    tr {
        page-break-inside: avoid;
    }

    .text-center {
        text-align: center;
    }

    .fw-bold {
        font-weight: bold;
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
        margin-bottom: 6px;
    }

    .mb-4 {
        margin-bottom: 10px;
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

    @page {
        margin-top: 80px;
        margin-bottom: 45px;
        margin-left: 35px;
        margin-right: 35px;
    }

    .verif-line {
        margin: 0 0 2px 0;
    }

    .verif-line:last-child {
        margin-bottom: 0;
    }

    ol {
        margin: 4px 0;
        padding-left: 16px;
    }

    ul.keterangan {
        list-style: none;
        padding-left: 0;
        margin: 4px 0;
    }
    </style>
</head>

<body>
    {{-- ===== HEADER FIXED ===== --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 25%; vertical-align: middle;">
                    <table style="border: none; border-collapse: collapse;">
                        <tr>
                            <td class="no-border" style="width: 20%; vertical-align: middle;">
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
                <td class="no-border" style="text-align: right; vertical-align: middle; font-size: 9px; font-weight: bold;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <h3 class="mb-2 text-center" style="text-transform: uppercase; margin: 4px 0 8px 0; font-size: 11px;">
        Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan
    </h3>

    <table style="width: 100%; border: none; margin-bottom: 6px;">
        <tr style="border: none;">
            <td style="text-align: left; border: none; padding: 0;">
                Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
        </tr>
    </table>

    <h3 style="margin: 4px 0; font-size: 10px;">Pemeriksaan Ruangan</h3>
    <table class="mb-4">
        <colgroup>
            <col style="width:3%">
            <col style="width:20%">
            <col style="width:6%">
            <col style="width:6%">
            <col style="width:15%">
            <col style="width:15%">
            <col style="width:35%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Area Produksi / Elemen</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($report->roomDetails->groupBy('room.name') as $roomName => $details)
            <tr>
                <td class="text-center fw-bold">{{ $no++ }}</td>
                <td class="fw-bold" colspan="6">{{ strtoupper($roomName) }}</td>
            </tr>
            @foreach ($details as $detail)
            <tr>
                <td></td>
                <td>{{ optional($detail->element)->element_name }}</td>
                <td class="text-center">
                    @if ($detail->condition === 'clean') ✔ @endif
                </td>
                <td class="text-center">
                    @if ($detail->condition === 'dirty') X @endif
                </td>
                <td>{{ $detail->notes }}</td>
                <td>{{ $detail->corrective_action }}</td>
                <td>
                    <div class="verif-line">V.Utama: {{ $detail->verification ?? '-' }} | {{ $detail->notes ?? '-' }} | {{ $detail->corrective_action ?? '-' }}</div>
                    @foreach($detail->followups as $index => $followup)
                    <div class="verif-line">Lanjutan #{{ $index + 1 }}: {{ $followup->verification ?? '-' }} | {{ $followup->notes ?? '-' }} | {{ $followup->corrective_action ?? '-' }}</div>
                    @endforeach
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <h3 style="margin: 4px 0; font-size: 10px;">Pemeriksaan Mesin & Peralatan</h3>
    <table>
        <colgroup>
            <col style="width:3%">
            <col style="width:20%">
            <col style="width:6%">
            <col style="width:6%">
            <col style="width:15%">
            <col style="width:15%">
            <col style="width:35%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Peralatan / Part</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($report->equipmentDetails->groupBy('equipment.name') as $equipmentName => $details)
            <tr>
                <td class="text-center fw-bold">{{ $no++ }}</td>
                <td class="fw-bold" colspan="6">{{ strtoupper($equipmentName) }}</td>
            </tr>
            @foreach ($details as $detail)
            <tr>
                <td></td>
                <td>{{ optional($detail->part)->part_name }}</td>
                <td class="text-center">
                    @if ($detail->condition === 'clean') ✔ @endif
                </td>
                <td class="text-center">
                    @if ($detail->condition === 'dirty') X @endif
                </td>
                <td>{{ $detail->notes }}</td>
                <td>{{ $detail->corrective_action }}</td>
                <td>
                    <div class="verif-line">V.Utama: {{ $detail->verification ?? '-' }} | {{ $detail->notes ?? '-' }} | {{ $detail->corrective_action ?? '-' }}</div>
                    @foreach($detail->followups as $index => $followup)
                    <div class="verif-line">Lanjutan #{{ $index + 1 }}: {{ $followup->verification ?? '-' }} | {{ $followup->notes ?? '-' }} | {{ $followup->corrective_action ?? '-' }}</div>
                    @endforeach
                </td>
            </tr>
            @endforeach
            @endforeach
            <tr>
                <td colspan="7" style="text-align: right; border: none;">{{ $formNumber ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin: 4px 0;"><strong>Keterangan:</strong></p>
    <ul class="keterangan">
        <li>✔ : Bersih dan bebas material non halal</li>
        <li>x : Kotor</li>
    </ul>

    <ol>
        <li>Berdebu</li>
        <li>Noda (karat, cat atau sejenisnya)</li>
        <li>Endapan kotoran</li>
        <li>Pertumbuhan mikroorganisme (jamur, bau busuk)</li>
        <li>Becek/menggenang</li>
    </ol>

    <table style="width: 100%; border: none; margin-top: 20px;">
        <tr style="border: none;">
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="60" style="margin: 6px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh:<br><br>
                @if($report->known_by)
                <img src="{{ $knownQr }}" width="60" style="margin: 6px 0;"><br>
                <strong>{{ $report->known_by }}</strong><br><br>
                @else
                <div style="height: 70px;"></div>
                <strong>-</strong><br>
                @endif
                SPV/Foreman/Lady Produksi
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="60" style="margin: 6px 0;"><br>
                <strong>{{ $report->approved_by }}</strong><br><br>
                @else
                <div style="height: 70px;"></div>
                <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

</body>

</html>