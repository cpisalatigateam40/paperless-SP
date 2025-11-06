<!DOCTYPE html>
<html>

<head>
    <title>Report Pasteur</title>
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

    <h3 class="mb-2 text-center">PEMERIKSAAN PASTEURISASI</h3>

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

    <table class="table table-bordered">
        <thead class="text-center align-middle">
            <tr>
                <th style="width: 220px;">Keterangan</th>
                @foreach($report->details as $detail)
                <th>{{ $detail->product->product_name ?? '-' }} - {{ $detail->product->nett_weight ?? '-' }} g</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Info Produk --}}
            <tr>
                <td>Nomor Program</td>
                @foreach($report->details as $detail)
                <td>{{ $detail->program_number ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Kode Produk</td>
                @foreach($report->details as $detail)
                <td>{{ $detail->product_code ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Untuk Kemasan (gr)</td>
                @foreach($report->details as $detail)
                <td>{{ $detail->for_packaging_gr ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jumlah Troly/Pack</td>
                @foreach($report->details as $detail)
                <td>{{ $detail->trolley_count ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Suhu Produk</td>
                @foreach($report->details as $detail)
                <td>{{ $detail->product_temp ?? '-' }}</td>
                @endforeach
            </tr>

            {{-- Step 1-7 --}}
            @php
            $standardSteps = [
            1 => 'Water Injection',
            2 => 'Up Temperature',
            3 => 'Pasteurisasi',
            4 => 'Hot Water Recycling',
            5 => 'Cooling Water Injection',
            6 => 'Cooling Constant Temp.',
            7 => 'Raw Cooling Water',
            ];
            @endphp

            @foreach($standardSteps as $order => $name)
            <tr class="bg-light">
                <td><strong>{{ $order }}. {{ $name }}</strong></td>
                @foreach($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Mulai (menit)</td>
                @foreach($report->details as $detail)
                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                <td>{{ $step->standardStep?->start_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Selesai (menit)</td>
                @foreach($report->details as $detail)
                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                <td>{{ $step->standardStep?->end_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Temp. Air (°C)</td>
                @foreach($report->details as $detail)
                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                <td>{{ $step->standardStep?->water_temp ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Pressure (MPa)</td>
                @foreach($report->details as $detail)
                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                <td>{{ $step->standardStep?->pressure ?? '-' }}</td>
                @endforeach
            </tr>
            @endforeach

            {{-- Step 8: Drainage --}}
            <tr class="bg-light">
                <td><strong>8. Drainage Pressure</strong></td>
                @foreach($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Mulai (menit)</td>
                @foreach($report->details as $detail)
                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                <td>{{ $drainage->drainageStep?->start_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Selesai (menit)</td>
                @foreach($report->details as $detail)
                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                <td>{{ $drainage->drainageStep?->end_time ?? '-' }}</td>
                @endforeach
            </tr>

            {{-- Step 9: Finish --}}
            <tr class="bg-light">
                <td><strong>9. Finish Produk</strong></td>
                @foreach($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            <tr>
                <td>Suhu Pusat Produk</td>
                @foreach($report->details as $detail)
                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                <td>{{ $finish->finishStep?->product_core_temp ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Sortasi</td>
                @foreach($report->details as $detail)
                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                <td>{{ $finish->finishStep?->sortation ?? '-' }}</td>
                @endforeach
            </tr>

            {{-- Paraf --}}
            <tr class="bg-light">
                <td><strong>Paraf</strong></td>
                @foreach($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            <tr>
                <td>QC</td>
                @foreach($report->details as $detail)
                <td>
                    @php
                    $qcBase64 = null;
                    if ($detail->qc_paraf) {
                    $qcPath = storage_path('app/public/' . $detail->qc_paraf);
                    if (file_exists($qcPath)) {
                    $type = pathinfo($qcPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($qcPath);
                    $qcBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                    }
                    @endphp

                    @if($qcBase64)
                    <img src="{{ $qcBase64 }}" alt="QC" width="60">
                    @endif
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Produksi</td>
                @foreach($report->details as $detail)
                <td>
                    @php
                    $prodBase64 = null;
                    if ($detail->production_paraf) {
                    $prodPath = storage_path('app/public/' . $detail->production_paraf);
                    if (file_exists($prodPath)) {
                    $type = pathinfo($prodPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($prodPath);
                    $prodBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                    }
                    @endphp

                    @if($prodBase64)
                    <img src="{{ $prodBase64 }}" alt="Produksi" width="60">
                    @endif
                </td>
                @endforeach
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