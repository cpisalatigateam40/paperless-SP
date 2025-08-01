<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Form PDF - Kebersihan Area Penyimpanan Bahan</title>
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

    @page {
        margin-top: 80px;
        size: 210mm 330mm;
    }

    ul {
        margin: unset;
        padding: .5rem;
    }

    li {
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
                                if(file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="vertical-align: middle; padding-left: 10px;">
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

    <h3 class="mb-2 text-center">KONDISI RUANG PENYIMPANAN BAHAN BAKU DAN PENUNJANG</h3>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="text-align: left; border: none;">
                Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
            <td style="text-align: left; border: none;">
                Shift: <span style="text-decoration: underline;"> {{ $report->shift }} </span>
            </td>
            <td style="text-align: left; border: none;">
                Area: <span style="text-decoration: underline;"> {{ $report->room_name }}</span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Jam</th>
                <th>No</th>
                <th>Item</th>
                <th>Kondisi</th>
                <th>Keterangan</th>
                <th>Tindakan Koreksi</th>
                <th>Verifikasi Setelah Dilakukan Tindakan Koreksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $detail)
            @foreach($detail->items as $i => $item)
            <tr>
                <td>{{ $detail->inspection_hour }}</td>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item }}</td>
                <td class="text-center">{{ $item->condition }}</td>
                <td>
                    @php $notes = json_decode($item->notes, true); @endphp
                    @if(is_array($notes))
                    {{ implode(', ', $notes) }}
                    @else
                    {{ $item->notes ?? '-' }}
                    @endif
                </td>
                <td>{{ $item->corrective_action ?? '-' }}</td>
                <td>
                    <ul>
                        <li><strong>Utama:</strong> {{ $item->verification ? 'OK' : 'Tidak OK' }}</li>
                        @foreach($item->followups as $index => $followup)
                        <li><strong>Lanjutan #{{ $index+1 }}:</strong> {{ $followup->verification ? 'OK' : 'Tidak OK' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
            @endforeach
            <tr>
                <td colspan="7" class="no-border" style="text-align: right;">QM 12 / 01</td>
            </tr>
        </tbody>

    </table>

    <p><strong>Keterangan:</strong></p>
    <ul style="padding-left: 20px;">
        <li>1. Tertata rapi</li>
        <li>2. Penempatan sesuai tagging dan jenis allergen</li>
        <li>3. Bersih dan bebas dari kontaminan</li>
        <li>4. Tidak tertata rapi</li>
        <li>5. Penempatan tidak sesuai tagging dan jenis allergen</li>
        <li>6. Tidak bersih / ada kontaminan</li>
    </ul>

    <br><br>

    <table style="width: 100%; border: none; margin-top: 4rem;">
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