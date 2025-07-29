<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export PDF - Loss Vacuum</title>
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

    <h3 style="text-align: center;">VERIFIKASI PRODUK LOSS VACUM</h3>

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
                <th>Jenis Produk</th>
                @foreach ($report->details as $detail)
                <td colspan="2">{{ $detail->product->product_name ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Kode Produksi</th>
                @foreach ($report->details as $detail)
                <td colspan="2">{{ $detail->production_code }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Mesin Vacum (Manual/Colimatic/CFS)</th>
                @foreach ($report->details as $detail)
                <td colspan="2">{{ $detail->vacum_machine }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Jumlah Sampel (pack)</th>
                @foreach ($report->details as $detail)
                <td colspan="2">{{ $detail->sample_amount }}</td>
                @endforeach
            </tr>
            <tr>
                <th rowspan="2">Hasil Pemeriksaan</th>
                @foreach ($report->details as $detail)
                @endforeach
            </tr>
            <tr>
                @foreach ($report->details as $detail)
                <th>Jumlah Pack</th>
                <th>%</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
            $categories = [
            'Produk bagus',
            'Seal tidak sempurna',
            'Melipat',
            'Casing terjepit',
            'Top bergeser',
            'Seal terlalu panas',
            'Seal kurang panas',
            'Sobek',
            'Isi per pack tidak sesuai',
            'Penataan produk tidak rapi',
            'Produk tidak utuh',
            'Lain-lain',
            ];
            @endphp

            @foreach ($categories as $cat)
            <tr>
                <td class="text-start">- {{ $cat }}</td>
                @foreach ($report->details as $detail)
                @php
                $def = $detail->defects->firstWhere('category', $cat);
                @endphp
                <td>{{ $def->pack_amount ?? '-' }}</td>
                <td>{{ $def->percentage ?? '-' }}</td>
                @endforeach
            </tr>
            @endforeach
            <tr>
                <td colspan="11" style="text-align: right; border: none;">QM 40 / 01</td>
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