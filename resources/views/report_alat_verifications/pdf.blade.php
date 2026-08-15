<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Alat Ukur</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 6px;
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 3px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .text-center { text-align: center; }
    .no-border { border: none !important; }
    .mb-2 { margin-bottom: 1rem; }
    .mb-3 { margin-bottom: 1.5rem; }
    .fw-bold { font-weight: bold; }

    .section-title {
        font-weight: bold;
        margin-bottom: 4px;
    }

    .info-table td {
        border: none;
        padding: 1px 0;
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

    tr, td, th { page-break-inside: avoid; }
    thead { display: table-header-group; }
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

    <h3 class="mb-2 text-center" style="text-transform: uppercase;">Verifikasi Alat Ukur</h3>

    {{-- ===== A. INFORMASI ===== --}}
    <div class="section-title">A. Informasi</div>
    <table class="info-table mb-3" style="width: 60%;">
        <tr>
            <td width="120">Hari, Tanggal</td>
            <td width="15">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>:</td>
            <td>{{ $report->shift }}</td>
        </tr>
        <tr>
            <td>Area</td>
            <td>:</td>
            <td>{{ $report->area->name ?? '-' }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>

    @php
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
        $alatTypeLabel = ['scale' => 'Timbangan', 'thermometer' => 'Thermometer'];
        $groupedDetails = $report->details->groupBy(fn($d) => $d->check_time ?? '-');
    @endphp

    @foreach($groupedDetails as $gIndex => $details)

    <table style="width: 100%; border: none; margin-bottom: 2px;">
        <tr style="border: none;">
            <td class="no-border fw-bold" style="width: 30%;">
                {{ $roman[$loop->index] ?? ($loop->index + 1) }}. Waktu Pemeriksaan
            </td>
            <td class="no-border">
                : {{ $details->first()->check_time ? \Carbon\Carbon::parse($details->first()->check_time)->format('H:i') : '-' }} WIB
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Jenis Alat</th>
                <th style="width: 20%;">Kode Alat</th>
                <th style="width: 20%;">Titik Ukur</th>
                <th style="width: 15%;">Nilai Baca (kg/°C)</th>
                <th style="width: 25%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $alatTypeLabel[$detail->alat_type] ?? $detail->alat_type }}</td>
                    <td>{{ $detail->alat->code ?? '-' }}</td>
                    <td>{{ $detail->titik_ukur }}</td>
                    <td class="text-center">{{ $detail->nilai_baca }}</td>
                    <td>{{ $detail->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @endforeach

    <br>

    {{-- ===== TANDA TANGAN ===== --}}
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