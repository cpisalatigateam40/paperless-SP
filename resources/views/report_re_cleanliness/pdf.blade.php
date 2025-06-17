<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan</title>
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

        th, td {
            border: 1px solid #000;
            padding: 2px 3px; /* lebih rapat */
            text-align: left;
            vertical-align: top;
        }

        th {
            text-align: center;
            font-weight: normal;
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

        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }

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

    <h3 class="mb-2 text-center">VERIFIKASI KEBERSIHAN RUANGAN, MESIN, DAN PERALATAN</h3>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="text-align: left; border: none;">
                Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
        </tr>
    </table>

    <h3>Pemeriksaan Ruangan</h3>
    <table class="table table-bordered table-sm mb-4 align-middle">
        <thead class="align-middle text-center">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Area Produksi / Elemen</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($report->roomDetails->groupBy('room.name') as $roomName => $details)
                {{-- Judul ruangan --}}
                <tr>
                    <td class="text-center fw-bold">{{ $no++ }}</td>
                    <td class="fw-bold" colspan="6">{{ strtoupper($roomName) }}</td>
                </tr>
                {{-- Elemen --}}
                @foreach ($details as $detail)
                    <tr>
                        <td></td>
                        <td>{{ optional($detail->element)->element_name }}</td>
                        <td class="text-center">
                            @if ($detail->condition === 'clean') ✔ @endif
                        </td>
                        <td class="text-center">
                            @if ($detail->condition === 'dirty') X @endif
                        </td>
                        <td>{{ $detail->notes }}</td>
                        <td>{{ $detail->corrective_action }}</td>
                        <td>{{ $detail->verification }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <h3>Pemeriksaan Mesin & Peralatan</h3>
    <table class="table table-bordered table-sm align-middle">
        <thead class="align-middle text-center">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Peralatan / Part</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($report->equipmentDetails->groupBy('equipment.name') as $equipmentName => $details)
                <tr>
                    <td class="text-center fw-bold">{{ $no++ }}</td>
                    <td class="fw-bold" colspan="6">{{ strtoupper($equipmentName) }}</td>
                </tr>
                @foreach ($details as $detail)
                    <tr>
                        <td></td>
                        <td>{{ optional($detail->part)->part_name }}</td>
                        <td class="text-center">
                            @if ($detail->condition === 'clean') ✔ @endif
                        </td>
                        <td class="text-center">
                            @if ($detail->condition === 'dirty') X @endif
                        </td>
                        <td>{{ $detail->notes }}</td>
                        <td>{{ $detail->corrective_action }}</td>
                        <td>{{ $detail->verification }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr>
                <td colspan="7" style="text-align: right; border: none;">QM 01 / 05</td>
            </tr>
        </tbody>
    </table>

    <p><strong>Keterangan:</strong></p>
    <ul style="list-style: none; padding-left: 0;">
        <li>✔ : Bersih dan bebas material non halal</li>
        <li>x : Kotor</li>
    </ul>

    <ol style="padding-left: 1.2rem;">
        <li>Berdebu</li>
        <li>Noda (karat, cat atau sejenisnya)</li>
        <li>Endapan kotoran</li>
        <li>Pertumbuhan mikroorganisme (jamur, bau busuk)</li>
        <li>Becek/menggenang</li>
    </ol>

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
