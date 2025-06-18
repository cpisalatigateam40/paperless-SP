<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pemeriksaan Kebersihan dan Sanitasi Setelah Perbaikan Mesin</title>
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

    <h3 class="mb-4 text-center">PEMERIKSAAN KEBERSIHAN DAN SANITASI SETELAH PERBAIKAN MESIN</h3>

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

    <br>

    <table>
        <thead>
            <tr>
                <th rowspan="3">Mesin / Peralatan</th>
                <th rowspan="3">Jenis Perbaikan</th>
                <th rowspan="3">Area</th>
                <th colspan="4">Kondisi Mesin Setelah Perbaikan</th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th colspan="2">Kebersihan</th>
                <th colspan="2">Spare Part yang</th>
            </tr>
            <tr>
                <th>Bersih</th>
                <th>Kotor</th>
                <th>Ada</th>
                <th>Tidak Ada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
            <tr>
                <td>{{ mb_convert_encoding($detail->equipment->name ?? '-', 'UTF-8', 'UTF-8') }}</td>
                <td>{{ mb_convert_encoding($detail->repair_type ?? '-', 'UTF-8', 'UTF-8') }}</td>
                <td>{{ mb_convert_encoding($detail->section->section_name ?? '-', 'UTF-8', 'UTF-8') }}</td>
                <td class="text-center">
                    {!! $detail->clean_condition === 'bersih' ? '&#10003;' : '' !!} {{-- ✓ --}}
                </td>
                <td class="text-center">
                    {!! $detail->clean_condition === 'kotor' ? '&#10007;' : '' !!} {{-- ✗ --}}
                </td>
                <td class="text-center">
                    {!! $detail->spare_part_left === 'ada' ? '&#10003;' : '' !!} {{-- ✓ --}}
                </td>
                <td class="text-center">
                    {!! $detail->spare_part_left === 'tidak ada' ? '&#10007;' : '' !!} {{-- ✗ --}}
                </td>
                <td>{{ mb_convert_encoding($detail->notes ?? '-', 'UTF-8', 'UTF-8') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="8" style="text-align: right; border: none;">QM 22 / 00</td>
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
