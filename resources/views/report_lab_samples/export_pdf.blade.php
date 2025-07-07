<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Lab Sample</title>
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

    <h3 class="mb-2 text-center">Report Lab Sample</h3>

    <table style="width: 100%; margin-bottom: 10px; border: none;">
        <tr>
            <td style="width: 60%; border: none;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Hari/Tanggal</strong></td>
                        <td style="border: none;">:
                            {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; vertical-align: top;"><strong>Sampel Storage</strong></td>
                        <td style="border: none;">
                            {{-- Frozen --}}
                            <input type="checkbox" {{ str_contains($report->storage, 'Frozen') ? 'checked' : '' }}>
                            Frozen (≤ -18°C)

                            {{-- Chilled --}}
                            <input type="checkbox" {{ str_contains($report->storage, 'Chilled') ? 'checked' : '' }}>
                            Chilled (0 - 5°C)

                            {{-- Other --}}
                            <input type="checkbox" {{ str_contains($report->storage, 'Other') ? 'checked' : '' }}>
                            Other
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; border: none; vertical-align: top; text-align: right;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Shift</strong></td>
                        <td style="border: none;">: {{ $report->shift }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kode Produksi</th>
                <th>Best Before</th>
                <th>Jumlah</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                <td>{{ $detail->production_code }}</td>
                <td>{{ $detail->best_before }}</td>
                <td>{{ $detail->quantity }}</td>
                <td>{{ $detail->notes }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="6" style="text-align: right; border: none;">QM 20 / 01</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 4rem;">
        <tr style="border: none;">
            <td style="text-align: center; border: none; width: 25%;">
                Diperiksa oleh,<br><br>
                <img src="{{ $createdQr }}" width="80" style="margin: 10px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 25%;">
                Diketahui oleh,<br><br>
                <div style="height: 50px;"></div>
                <strong>{{ $report->known_by }}</strong><br>
                Foreman / SPV Produksi
            </td>
            <td style="text-align: center; border: none; width: 25%;">
                Diterima oleh,<br><br>
                <div style="height: 50px;"></div>
                <strong>{{ $report->accepted_by }}</strong><br>
                Petugas LAB
            </td>
            <td style="text-align: center; border: none; width: 25%;">
                Disetujui oleh,<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="80" style="margin: 10px 0;"><br>
                <strong>{{ $report->approved_by }}</strong><br>
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