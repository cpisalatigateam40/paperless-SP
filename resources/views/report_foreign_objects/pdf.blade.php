<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pemeriksaan Kontaminasi Benda Asing</title>
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

    <h3 class="mb-2 text-center">PEMERIKSAAN KONTAMINASI BENDA ASING</h3>

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
                Area: <span style="text-decoration: underline;"> {{ $report->section->section_name }}</span>
            </td>
        </tr>
    </table>

    <table style="margin-top: 10px;">
        <thead>
            <tr>
                <th class="align-middle">Jam</th>
                <th class="align-middle">Nama Produk</th>
                <th class="align-middle">Kode Produksi</th>
                <th class="align-middle">Jenis Kontaminan</th>
                <th class="align-middle">Bukti (Foto)</th>
                <th class="align-middle">Tahapan</th>
                <th class="align-middle">Analisis Asal Kontaminan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
            <tr>
                <td>{{ $detail->time }}</td>
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                <td>{{ $detail->production_code }}</td>
                <td>{{ $detail->contaminant_type }}</td>
                <td class="text-center">
                    @php
                    $evidencePath = storage_path('app/public/' . $detail->evidence);
                    $evidenceBase64 = null;

                    if ($detail->evidence && file_exists($evidencePath)) {
                    $type = pathinfo($evidencePath, PATHINFO_EXTENSION);
                    $data = file_get_contents($evidencePath);
                    $evidenceBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                    @endphp

                    @if($evidenceBase64)
                    <img src="{{ $evidenceBase64 }}" width="50">
                    @else
                    -
                    @endif
                </td>
                <td>{{ $detail->analysis_stage }}</td>
                <td>{{ $detail->contaminant_origin }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="7" style="text-align: right; border: none;">QM 09 / 00</td>
            </tr>
        </tbody>
    </table>

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