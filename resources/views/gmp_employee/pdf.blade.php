<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan GMP</title>
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

    <h3 class="mb-2 text-center">KONTROL SANITASI</h3>

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

    <h4>KEBERSIHAN KARYAWAN</h4>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Jam</th>
                <th>Area</th>
                <th>Nama Karyawan</th>
                <th>Keterangan</th>
                <th>Tindakan Koreksi</th>
                <th>Verifikasi & Koreksi Lanjutan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->inspection_hour }}</td>
                <td>{{ $detail->section_name }}</td>
                <td>{{ $detail->employee_name }}</td>
                <td>{{ $detail->notes }}</td>
                <td>{{ $detail->corrective_action }}</td>
                <td>
                    <ul style="margin: 0; padding-left: 16px;">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $detail->verification ? 'OK' : 'Tidak OK' }}<br>
                            Keterangan: {{ $detail->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $detail->corrective_action ?? '-' }}
                        </li>
                        @foreach($detail->followups as $index => $followup)
                        <li style="margin-top: 4px;">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification ? 'OK' : 'Tidak OK' }}<br>
                            Keterangan: {{ $followup->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $followup->action ?? '-' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="7" style="text-align: right; border: none;">QM 13 / 01</td>
            </tr>
        </tbody>
    </table>
    <p>Ket : * meliput Boot, Seragam, Topi, Masker dan Sarung tangan.</p>

    @if($report->sanitationCheck)
    <h4>KONTROL SANITASI</h4>
    @php
    $firstCheck = $report->sanitationCheck;
    $hour1 = $firstCheck?->hour_1 ?? 'Jam 1';
    $hour2 = $firstCheck?->hour_2 ?? 'Jam 2';
    $areas = $report->sanitationCheck->sanitationArea ?? [];
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
        <thead>
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Area</th>
                <th rowspan="3">Std Klorin (ppm)</th>
                <th colspan="4">Hasil Pengecekan</th>
                <th rowspan="3">Keterangan</th>
                <th rowspan="3">Tindakan Koreksi</th>
                <th rowspan="3">Verifikasi & Koreksi Lanjutan</th>
            </tr>
            <tr>
                <th colspan="2">Jam 1: {{ $hour1 }}</th>
                <th colspan="2">Jam 2: {{ $hour2 }}</th>
            </tr>
            <tr>
                <th>Kadar Klorin (ppm)</th>
                <th>Suhu (°C)</th>
                <th>Kadar Klorin (ppm)</th>
                <th>Suhu (°C)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($areas as $area)
            @php
            $jam1 = $area->sanitationResult->firstWhere('hour_to', 1);
            $jam2 = $area->sanitationResult->firstWhere('hour_to', 2);
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $area->area_name }}</td>
                <td>{{ $area->chlorine_std ?? '-' }}</td>
                <td>{{ $jam1?->chlorine_level ?? '-' }}</td>
                <td>{{ $jam1?->temperature ?? '-' }}</td>
                <td>{{ $jam2?->chlorine_level ?? '-' }}</td>
                <td>{{ $jam2?->temperature ?? '-' }}</td>
                <td>{{ $area->notes ?? '-' }}</td>
                <td>{{ $area->corrective_action ?? '-' }}</td>
                <td>
                    <ul style="margin: 0; padding-left: 16px;">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $area->verification ? 'OK' : 'Tidak OK' }}<br>
                            Keterangan: {{ $area->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $area->corrective_action ?? '-' }}
                        </li>
                        @foreach($area->followups as $index => $followup)
                        <li style="margin-top: 4px;">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification ? 'OK' : 'Tidak OK' }}<br>
                            Keterangan: {{ $followup->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $followup->action ?? '-' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="10" style="text-align: right; border: none;">QM 02 / 00</td>
            </tr>
        </tbody>
    </table>
    @endif


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