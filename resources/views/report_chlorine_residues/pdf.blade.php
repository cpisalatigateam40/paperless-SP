<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Residu Klorin</title>
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
        padding: .2rem;
    }

    li {
        list-style-type: none;
    }
    </style>
</head>

<body>
    {{-- Header --}}
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

    <h3 class="text-center">VERIFIKASI RESIDU KLORIN</h3>

    <table class="header-table" style="margin-bottom: 10px;">
        <tr>
            <td class="no-border"><strong>AREA</strong></td>
            <td class="no-border">: {{ $report->area->area_name ?? '-' }}</td>
            <td class="no-border"><strong>BULAN</strong></td>
            <td class="no-border">: {{ \Carbon\Carbon::parse($report->month)->format('F Y') }}</td>
        </tr>
        <tr>
            <td class="no-border"><strong>TITIK SAMPLING</strong></td>
            <td class="no-border">: {{ $report->sampling_point ?? '-' }}</td>
            <td class="no-border"></td>
            <td class="no-border"></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">TANGGAL</th>
                <th rowspan="2">STANDAR (PPM)</th>
                <th rowspan="2">HASIL PEMERIKSAAN (PPM)</th>
                <th rowspan="2">KETERANGAN*</th>
                <th rowspan="2">TINDAKAN KOREKSI</th>
                <th rowspan="2">VERIFIKASI</th>
                <th colspan="2">DIVERIFIKASI OLEH</th>
            </tr>
            <tr>
                <th>NAMA</th>
                <th>PARAF</th>
            </tr>
        </thead>
        <tbody>
            @php $rowspan = $report->details->count(); @endphp
            @foreach($report->details as $index => $detail)
            <tr>
                <td class="text-center">{{ $detail->day }}</td>
                <td class="text-center">0,1 - 5</td>
                <td class="text-center">{{ $detail->result_ppm ?? '' }}</td>
                <td class="text-center">{{ $detail->remark ?? '' }}</td>
                <td class="text-center">{{ $detail->corrective_action ?? '' }}</td>
                <td>
                    <ul>
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $detail->verification ?? '-' }}<br>
                            Tindakan Koreksi: {{ $detail->corrective_action ?? '-' }}
                        </li>

                        @foreach($detail->followups as $findex => $followup)
                        <li class="mt-1">
                            <strong>Koreksi Lanjutan #{{ $findex + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification ?? '-' }}<br>
                            Catatan: {{ $followup->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $followup->corrective_action ?? '-' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
                <td class="text-center">{{ $detail->verified_by ?? '' }}</td>
                <td class="text-center">
                    @if($detail->verified_at)
                    {{ \Carbon\Carbon::parse($detail->verified_at)->format('d-m-Y') }}
                    @endif
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="8" style="text-align: right; border: none;">QM 48 / 00</td>
            </tr>
        </tbody>
    </table>


    <p class="small-text">*) OK/TIDAK OK</p>

    <table style="width: 100%; border: none; margin-top: 4rem;">
        <tr style="border: none;">
            <td style="text-align: right; border: none; width: 33%;">
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