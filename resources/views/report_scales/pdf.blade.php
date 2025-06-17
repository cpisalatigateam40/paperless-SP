<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemeriksaan</title>
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

    <h3 class="mb-2 text-center">LAPORAN PEMERIKSAAN TIMBANGAN & THERMOMETER</h3>

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

    {{-- Timbangan --}}
    <h5>1.PEMERIKSAAN TIMBANGAN</h5>
    <table>
        <thead>
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Jenis dan Kode Timbangan</th>
                <th colspan="3">
                    Pemeriksaan Pukul:
                    {{ optional($report->details->pluck('time_1')->filter()->first()) ? \Carbon\Carbon::parse($report->details->pluck('time_1')->filter()->first())->format('H:i') : '-' }}
                </th>
                <th colspan="3">
                    Pemeriksaan Pukul:
                    {{ optional($report->details->pluck('time_2')->filter()->last()) ? \Carbon\Carbon::parse($report->details->pluck('time_2')->filter()->last())->format('H:i') : '-' }}
                </th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th colspan="3">Standart Berat</th>
                <th colspan="3">Standart Berat</th>
            </tr>
            <tr>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
                <th>1000 Gr</th>
                <th>5000 Gr</th>
                <th>10000 Gr</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $i => $d)
                @php
                    $m1 = $d->measurements->where('inspection_time_index', 1)->keyBy('standard_weight');
                    $m2 = $d->measurements->where('inspection_time_index', 2)->keyBy('standard_weight');
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $d->scale->type ?? '' }} - {{ $d->scale->code ?? '' }}</td>
                    <td>{{ $m1->get(1000)->measured_value ?? '' }}</td>
                    <td>{{ $m1->get(5000)->measured_value ?? '' }}</td>
                    <td>{{ $m1->get(10000)->measured_value ?? '' }}</td>
                    <td>{{ $m2->get(1000)->measured_value ?? '' }}</td>
                    <td>{{ $m2->get(5000)->measured_value ?? '' }}</td>
                    <td>{{ $m2->get(10000)->measured_value ?? '' }}</td>
                    <td>{{ $d->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Thermometer --}}
    <h5 style="margin-top:30px;">2.PEMERIKSAAN THERMOMETER</h5>
    <table>
        <thead>
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Jenis dan Kode Timbangan</th>
                <th colspan="2">
                    Pemeriksaan Pukul:
                    {{ optional($report->thermometerDetails->pluck('time_1')->filter()->first()) ? \Carbon\Carbon::parse($report->thermometerDetails->pluck('time_1')->filter()->first())->format('H:i') : '-' }}
                </th>
                <th colspan="2">
                    Pemeriksaan Pukul:
                    {{ optional($report->thermometerDetails->pluck('time_2')->filter()->last()) ? \Carbon\Carbon::parse($report->thermometerDetails->pluck('time_2')->filter()->last())->format('H:i') : '-' }}
                </th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th colspan="2">Standart Suhu</th>
                <th colspan="2">Standart Suhu</th>
            </tr>
            <tr>
                <th>0°C</th>
                <th>100°C</th>
                <th>0°C</th>
                <th>100°C</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->thermometerDetails as $i => $d)
                @php
                    $m1 = $d->measurements->where('inspection_time_index', 1)->keyBy('standard_temperature');
                    $m2 = $d->measurements->where('inspection_time_index', 2)->keyBy('standard_temperature');
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $d->thermometer->type ?? '' }} - {{ $d->thermometer->code ?? '' }}</td>
                    <td>{{ $m1->get(0)->measured_value ?? '' }}</td>
                    <td>{{ $m1->get(100)->measured_value ?? '' }}</td>
                    <td>{{ $m2->get(0)->measured_value ?? '' }}</td>
                    <td>{{ $m2->get(100)->measured_value ?? '' }}</td>
                    <td>{{ $d->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Ket : √ = Ok <br> X = Tidak OK</p>

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
