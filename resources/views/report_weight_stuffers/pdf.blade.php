<!DOCTYPE html>
<html>

<head>
    <title>Laporan Verifikasi Berat Stuffer</title>
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

    ul {
        margin: unset;
        padding: .2rem;
    }

    li {
        list-style-type: none;
        margin-left: -1rem;
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

    <h3 class="mb-2 text-center">VERIFIKASI BERAT STUFFER</h3>

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
    $colspan = $report->details->count() * 2; // 2 kolom per produk (Townsend & Hitech)
    @endphp

    <table>
        <tr>
            <th rowspan="3">Keterangan</th>
            @foreach($report->details as $detail)
            <th colspan="2">{{ $detail->product->product_name ?? '-' }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($report->details as $detail)
            <th colspan="2">{{ $detail->production_code }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($report->details as $detail)
            <th colspan="2">{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</th>
            @endforeach
        </tr>

        {{-- Label Mesin --}}
        <tr>
            <th>Mesin Stuffer</th>
            @foreach($report->details as $detail)
            <th>Townsend</th>
            <th>Hitech</th>
            @endforeach
        </tr>

        {{-- Kecepatan --}}
        <tr>
            <th>Kecepatan Stuffer (rpm)</th>
            @foreach($report->details as $detail)
            <td>{{ $detail->townsend?->stuffer_speed ?? '-' }}</td>
            <td>{{ $detail->hitech?->stuffer_speed ?? '-' }}</td>
            @endforeach
        </tr>

        {{-- Ukuran Casing --}}
        <tr>
            <th>Ukuran Casing<br>(Panjang / Diameter)</th>
            @foreach($report->details as $detail)
            @php
            $caseT = $detail->cases->get(0);
            $caseH = $detail->cases->get(1);
            @endphp
            <td>{{ $caseT?->actual_case_1 ?? '-' }} / {{ $caseT?->actual_case_2 ?? '-' }}</td>
            <td>{{ $caseH?->actual_case_1 ?? '-' }} / {{ $caseH?->actual_case_2 ?? '-' }}</td>
            @endforeach
        </tr>

        {{-- Trolley --}}
        <tr>
            <th>Jumlah Trolley</th>
            @foreach($report->details as $detail)
            <td>{{ $detail->townsend?->trolley_total ?? '-' }}</td>
            <td>{{ $detail->hitech?->trolley_total ?? '-' }}</td>
            @endforeach
        </tr>

        {{-- Standar Berat --}}
        <tr>
            <th>Standar Berat (gr)</th>
            @foreach($report->details as $detail)
            <td colspan="2">{{ $detail->weight_standard ?? '-' }}</td>
            @endforeach
        </tr>

        {{-- Berat Aktual --}}
        <tr>
            <th>Berat Aktual (gr)</th>
            @foreach($report->details as $detail)
            @php
            $wT = $detail->weights->get(0);
            $wH = $detail->weights->get(1);
            @endphp
            <td>
                {{ $wT?->actual_weight_1 ?? '-' }} /
                {{ $wT?->actual_weight_2 ?? '-' }} /
                {{ $wT?->actual_weight_3 ?? '-' }}
            </td>
            <td>
                {{ $wH?->actual_weight_1 ?? '-' }} /
                {{ $wH?->actual_weight_2 ?? '-' }} /
                {{ $wH?->actual_weight_3 ?? '-' }}
            </td>
            @endforeach
        </tr>

        {{-- Rata-rata --}}
        <tr>
            <th>Rata-rata Berat Aktual (gr)</th>
            @foreach($report->details as $detail)
            <td>{{ $detail->townsend?->avg_weight ?? '-' }}</td>
            <td>{{ $detail->hitech?->avg_weight ?? '-' }}</td>
            @endforeach
        </tr>

        {{-- Catatan --}}
        <tr>
            <th>Catatan</th>
            @foreach($report->details as $detail)
            <td>{{ $detail->townsend?->notes ?? '-' }}</td>
            <td>{{ $detail->hitech?->notes ?? '-' }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="11" style="text-align: right; border: none;">QM 27 / 00</td>
        </tr>
    </table>

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