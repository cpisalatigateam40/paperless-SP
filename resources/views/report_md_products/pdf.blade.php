<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Metal Detector</title>
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

    <h3 class="mb-2 text-center">Report Pemeriksaan Metal Detector Produk</h3>

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
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Waktu</th>
                <th rowspan="2">Produk</th>
                <th rowspan="2">Kode Produksi</th>
                <th rowspan="2">Gramase</th>
                <th rowspan="2">Best Before</th>
                <th rowspan="2">No Program</th>
                <th colspan="4">Fe 1.5 mm</th>
                <th colspan="4">Non Fe 2 mm</th>
                <th colspan="4">SUS 2.5 mm</th>
                <th rowspan="2">Tindakan Perbaikan</th>
                <th rowspan="2">Verifikasi</th>
            </tr>
            <tr>
                <th>D</th>
                <th>T</th>
                <th>B</th>
                <th>DL</th>
                <th>D</th>
                <th>T</th>
                <th>B</th>
                <th>DL</th>
                <th>D</th>
                <th>T</th>
                <th>B</th>
                <th>DL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
            <tr>
                <td>{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</td>
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                <td>{{ $detail->production_code }}</td>
                <td>{{ $detail->gramase }}</td>
                <td>{{ $detail->best_before }}</td>
                <td>{{ $detail->program_number }}</td>
                @php
                $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
                $positions = ['d', 't', 'b', 'dl'];
                @endphp
                @foreach ($specimens as $specimen)
                @foreach ($positions as $pos)
                @php
                $posDetail = $detail->positions->where('specimen', $specimen)->where('position', $pos)->first();
                @endphp
                <td>{{ $posDetail ? ($posDetail->status ? '✓' : '×') : '-' }}</td>
                @endforeach
                @endforeach
                <td>{{ $detail->corrective_action }}</td>
                <td>{{ $detail->verification ? 'OK' : 'Tidak OK' }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="20" style="text-align: right; border: none;">QM 17 / 01</td>
            </tr>
        </tbody>
    </table>

    <p>D = depan ; T = tengah ; B = belakang, DL = dalam tumpukan produk</p>

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