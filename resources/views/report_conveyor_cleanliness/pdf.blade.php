<!DOCTYPE html>
<html>
<head>
    <title>Laporan pemeriksaan kebersihan conveyor packing</title>
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

    <h3 class="mb-2 text-center">PEMERIKSAAN KEBERSIHAN CONVEYOR PACKING</h3>

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

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Pukul</th>
                <th rowspan="2">Area Conveyor Mesin</th>
                <th colspan="2">Kondisi</th>
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
                <th rowspan="2">Verifikasi</th>
                <th>Dicek Oleh</th>
                <th>Diketahui Oleh</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
                <th>QC</th>
                <th>KR</th>
            </tr>
        </thead>
        <tbody>
            @php $grouped = $report->machines->chunk(4); @endphp
            @foreach ($grouped as $groupIndex => $group)
                @php $innerIndex = 0; @endphp
                @foreach ($group as $machine)
                    <tr>
                        <td>{{ $innerIndex === 0 ? $groupIndex + 1 : '' }}</td>
                        <td>{{ $machine->time ? \Illuminate\Support\Carbon::parse($machine->time)->format('H:i') : '-' }}</td>
                        <td class="text-start">{{ $machine->machine_name }}</td>
                        <td>{!! $machine->status === 'bersih' ? '&#10003;' : '' !!}</td>
                        <td>{!! $machine->status === 'kotor' ? '&#10007;' : '' !!}</td>
                        <td>{{ $machine->notes ?? '-' }}</td>
                        <td>{{ $machine->corrective_action ?? '-' }}</td>
                        <td>{{ $machine->verification ?? '-' }}</td>
                        <td>{!! $machine->qc_check ? '&#10003;' : '' !!}</td>
                        <td>{!! $machine->kr_check ? '&#10003;' : '' !!}</td>
                    </tr>
                    @php $innerIndex++; @endphp
                @endforeach
            @endforeach
            <tr>
                <td colspan="10" style="text-align: right; border: none;">QM 26 / 00</td>
            </tr>
        </tbody>
    </table>

    <p>Keterangan: &#10003; = OK / Bersih, &#10007; = Tidak OK / Kotor</p>

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
