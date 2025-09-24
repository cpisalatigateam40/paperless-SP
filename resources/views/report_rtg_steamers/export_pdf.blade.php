<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title> Laporan Pemasakan Dengan Steamer</title>
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

    <h3 class="mb-2 text-center">PEMERIKSAAN PEMASAKAN DENGAN STEAMER</h3>

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
                Produk: <span style="text-decoration: underline;"> {{ $report->product->product_name ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <table class="table table-bordered align-middle small text-center">
        <tbody>
            <tr>
                <th>Steamer</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->steamer }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Kode Prod.</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->production_code }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Jumlah Trolly</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->trolley_count }}</td>
                @endforeach
            </tr>
            <tr>
                <th>T. Ruang (°C)</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->room_temp }}</td>
                @endforeach
            </tr>
            <tr>
                <th>T. Produk (°C)</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->product_temp }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Waktu (Menit)</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->time_minute }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Jam Mulai</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->start_time }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Jam Selesai</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->end_time }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Kematangan</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensory_ripeness }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Rasa</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensory_taste }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Aroma</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensory_aroma }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Tekstur</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensory_texture }}</td>
                @endforeach
            </tr>
            <tr>
                <th>Warna</th>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensory_color }}</td>
                @endforeach
            </tr>
            <!-- <tr>
                <th>Paraf QC</th>
                @foreach ($report->details as $detail)
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
                <th>Paraf Produksi</th>
                @foreach ($report->details as $detail)
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
            </tr> -->
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