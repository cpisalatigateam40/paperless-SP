<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Form PDF - Kebersihan Area Proses</title>
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
        vertical-align: top;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .no-border {
        border: none !important;
    }

    .text-center {
        text-align: center;
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

    ul,
    li {
        margin: 0;
        padding: 2px;
        page-break-inside: avoid;
        list-style-type: none;
    }

    tr,
    td,
    th {
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    @page {
        margin-top: 80px;
        size: A4;
    }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 30%;">
                    <table style="border: none;">
                        <tr>
                            <td class="no-border" style="width: 50px;">
                                @php
                                $path = public_path('storage/image/logo.png');
                                if (file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="padding-left: 10px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    CHAROEN<br>POKPHAND<br>INDONESIA PT.<br>Food Division
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <h3 class="text-center">PEMERIKSAAN KONDISI KEBERSIHAN</h3>

    <table style="border: none;">
        <tr>
            <td class="no-border">Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
            <td class="no-border">Shift:
                <span style="text-decoration: underline;">{{ $report->shift }}</span>
            </td>
            <td class="no-border">Area:
                <span style="text-decoration: underline;">{{ $report->section_name }}</span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Pukul</th>
                <th rowspan="2">Item</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Hasil Verifikasi</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $index => $detail)
            @foreach($detail->items as $i => $item)
            <tr>
                @if($i === 0)
                <td rowspan="{{ count($detail->items) }}">{{ $index + 1 }}</td>
                <td rowspan="{{ count($detail->items) }}">{{ $detail->inspection_hour }}</td>
                @endif

                <td>{{ $item->item }}</td>

                @if(Str::startsWith($item->item, 'Suhu ruang'))
                <td colspan="2" class="text-center">
                    Actual: {{ $item->temperature_actual ?? '-' }} ℃<br>
                    Display: {{ $item->temperature_display ?? '-' }} ℃
                </td>
                @else
                <td class="text-center">{{ strtolower($item->condition) === 'bersih' ? '✓' : '' }}</td>
                <td class="text-center">{{ strtolower($item->condition) === 'kotor' ? 'x' : '' }}</td>
                @endif


                <td>{{ $item->notes ?? '-' }}</td>
                <td>{{ $item->corrective_action ?? '-' }}</td>
                <td>
                    <ul>
                        <li>
                            <strong>Verifikasi Utama:</strong>
                            {{ $item->verification ? 'OK' : 'Tidak OK' }}
                        </li>
                        @foreach($item->followups as $fIndex => $followup)
                        <li>
                            <strong>Koreksi Lanjutan #{{ $fIndex+1 }}:</strong>
                            {{ $followup->verification ? 'OK' : 'Tidak OK' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
            @endforeach
            <tr>
                <td colspan="8" class="no-border" style="text-align: right;">QM 13 / 01</td>
            </tr>
        </tbody>
    </table>


    <p><strong>Keterangan:</strong></p>
    <ul style="padding-left: 20px;">
        <li>✓: OK/bersih</li>
        <li>x: Tidak OK/kotor</li>
    </ul>

    <br><br>

    <table style="width: 100%; border: none; margin-top: 4rem;">
        <tr>
            <td class="no-border text-center" style="width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="80"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td class="no-border text-center" style="width: 33%;">
                Diketahui oleh:<br><br>
                @if($report->known_by)
                <img src="{{ $knownQr }}" width="80"><br>
                <strong>{{ $report->known_by }}</strong><br>
                @else
                <div style="height: 80px;"></div>
                <strong>-</strong><br>
                @endif
                SPV/Foreman/Lady Produksi
            </td>
            <td class="no-border text-center" style="width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="80"><br>
                <strong>{{ $report->approved_by }}</strong><br>
                @else
                <div style="height: 80px;"></div>
                <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>
</body>

</html>