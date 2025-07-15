<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Stuffer - {{ $report->date }}</title>
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

    <h3 class="text-center mb-3">FORM REKAP STUFFER DAN COOKING LOSS</h3>

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

    <div class="mb-3">
        <h3>Rekap Stuffer</h3>
        <table class="text-center">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Produk</th>
                    <th rowspan="2">Standar Berat (gram)</th>
                    <th colspan="2">HITECH</th>
                    <th colspan="2">TOWNSEND</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th>Range</th>
                    <th>Avg</th>
                    <th>Range</th>
                    <th>Avg</th>
                </tr>
            </thead>
            <tbody>
                @php $grouped = $report->detailStuffers->groupBy('product_uuid'); @endphp
                @forelse ($grouped as $product_uuid => $items)
                    @php
                        $product = optional($items->first()->product)->product_name ?? '-';
                        $standard = $items->first()->standard_weight ?? '-';
                        $note = $items->first()->note ?? '-';
                        $hitech = $items->firstWhere('machine_name', 'Hitech');
                        $townsend = $items->firstWhere('machine_name', 'Townsend');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product }}</td>
                        <td>{{ $standard }}</td>
                        <td>{{ $hitech->range ?? '-' }}</td>
                        <td>{{ $hitech->avg ?? '-' }}</td>
                        <td>{{ $townsend->range ?? '-' }}</td>
                        <td>{{ $townsend->avg ?? '-' }}</td>
                        <td>{{ $note }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Tidak ada data</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="8" style="text-align: right; border: none;">QM 41 / 00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div>
        <h3>Cooking Loss</h3>
        <table class="text-center">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>% Cooking Loss Fessmann</th>
                    <th>% Cooking Loss Maurer</th>
                </tr>
            </thead>
            <tbody>
                @php $groupedLoss = $report->cookingLossStuffers->groupBy('product_uuid'); @endphp
                @forelse ($groupedLoss as $product_uuid => $items)
                    @php
                        $product = optional($items->first()->product)->product_name ?? '-';
                        $fessmann = $items->firstWhere('machine_name', 'Fessmann');
                        $maurer = $items->firstWhere('machine_name', 'Maurer');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product }}</td>
                        <td>{{ $fessmann->percentage ?? '-' }}</td>
                        <td>{{ $maurer->percentage ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Tidak ada data</td>
                    </tr>


                @endforelse
            </tbody>
        </table>
    </div>

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
                <div style="height: 50px;"></div>
                <strong>{{ $report->known_by }}</strong><br>
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