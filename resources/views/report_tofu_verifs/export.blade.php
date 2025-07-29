<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export PDF - Laporan Verifikasi Produk Tofu</title>
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

    <h3 class="mb-2 text-center">VERIFIKASI PRODUK TOFU</h3>

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

    @php
    $products = $report->productInfos;
    $weights = $report->weightVerifs->chunk(3);
    $defects = $report->defectVerifs->chunk(6);

    function getValue($weights, $category, $i, $field)
    {
    return optional($weights[$i]?->firstWhere('weight_category', $category))->$field ?? '-';
    }

    function getDefect($defects, $type, $i, $field)
    {
    return optional($defects[$i]?->firstWhere('defect_type', $type))->$field ?? '-';
    }

    $defectTypes = [
    'hole' => 'Berlubang',
    'stain' => 'Noda',
    'asymmetry' => 'Bentuk tidak bulat simetris',
    'other' => 'Lain-lain',
    'good' => 'Produk bagus',
    'note' => 'Keterangan',
    ];
    @endphp

    <table class="table table-bordered table-sm text-center align-middle">
        <tbody>
            {{-- Row: Kode Produksi --}}
            <tr>
                <td class="text-start">Kode Produksi</td>
                @foreach($products as $p)
                <td>{{ $p->production_code }}</td>
                @endforeach
            </tr>

            {{-- Row: Expired Date --}}
            <tr>
                <td class="text-start">Expired Date</td>
                @foreach($products as $p)
                <td>{{ $p->expired_date }}</td>
                @endforeach
            </tr>

            {{-- Row: Jumlah Sampel --}}
            <tr>
                <td class="text-start">Jumlah Sampel (pcs)</td>
                @foreach($products as $p)
                <td>{{ $p->sample_amount }}</td>
                @endforeach
            </tr>

            {{-- Header: Pemeriksaan Berat --}}
            <tr class="table-light">
                <th class="text-start" colspan="{{ $products->count() + 1 }}">Pemeriksaan Berat</th>
            </tr>

            <tr>
                <td class="text-start">- Under (&lt; 11gr/pc)</td>
                @foreach ($products as $i => $p)
                <td>
                    Turus: {{ getValue($weights, 'under', $i, 'turus') }}<br>
                    Jumlah: {{ getValue($weights, 'under', $i, 'total') }}<br>
                    %: {{ getValue($weights, 'under', $i, 'percentage') }}
                </td>
                @endforeach
            </tr>
            <tr>
                <td class="text-start">- Standard (11 - 13gr/pc)</td>
                @foreach ($products as $i => $p)
                <td>
                    Turus: {{ getValue($weights, 'standard', $i, 'turus') }}<br>
                    Jumlah: {{ getValue($weights, 'standard', $i, 'total') }}<br>
                    %: {{ getValue($weights, 'standard', $i, 'percentage') }}
                </td>
                @endforeach
            </tr>
            <tr>
                <td class="text-start">- Over (&gt; 13gr/pc)</td>
                @foreach ($products as $i => $p)
                <td>
                    Turus: {{ getValue($weights, 'over', $i, 'turus') }}<br>
                    Jumlah: {{ getValue($weights, 'over', $i, 'total') }}<br>
                    %: {{ getValue($weights, 'over', $i, 'percentage') }}
                </td>
                @endforeach
            </tr>

            {{-- Header: Pemeriksaan Defect --}}
            <tr class="table-light">
                <th class="text-start" colspan="{{ $products->count() + 1 }}">Pemeriksaan Defect</th>
            </tr>

            @foreach ($defectTypes as $key => $label)
            <tr>
                <td class="text-start">- {{ $label }}</td>
                @foreach ($products as $i => $p)
                <td>
                    Turus: {{ getDefect($defects, $key, $i, 'turus') }}<br>
                    Jumlah: {{ getDefect($defects, $key, $i, 'total') }}<br>
                    %: {{ getDefect($defects, $key, $i, 'percentage') }}
                </td>
                @endforeach
            </tr>
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right; border: none;">QM 23 / 00</td>
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