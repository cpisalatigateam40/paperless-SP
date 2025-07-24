<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Emulsion PDF</title>
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

    <h3 class="mb-2 text-center">VERIFIKASI PEMBUATAN EMULSI / CCM BLOCK</h3>

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

    <table class="table table-sm table-bordered text-center">
        <thead>
            {{-- Baris 1: Jenis Emulsi --}}
            <tr>
                <th style="width: 200px;" colspan="2">JENIS EMULSI</th>
                @foreach($report->header->agings ?? [] as $idx => $aging)
                <td colspan="2">{{ $report->header->emulsion_type ?? '-' }}</td>
                @endforeach
            </tr>
            {{-- Baris 2: Kode Produksi --}}
            <tr>
                <th colspan="2">KODE PRODUKSI</th>
                @foreach($report->header->agings ?? [] as $idx => $aging)
                <td colspan="2">{{ $report->header->production_code ?? '-' }}</td>
                @endforeach
            </tr>
            {{-- Baris 3: header kolom detail --}}
            <tr>
                <th rowspan="2">BAHAN BAKU</th>
                <th rowspan="2">Berat (kg)</th>
                @foreach($report->header->agings ?? [] as $idx => $aging)
                <th colspan="2">Emulsi {{ $idx + 1 }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($report->header->agings ?? [] as $aging)
                <th>Suhu (°C)</th>
                <th>Sensory</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Detail bahan baku --}}
            @foreach($report->header->details ?? [] as $detail)
            <tr>
                <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
                <td>{{ $detail->weight ?? '-' }}</td>
                @foreach($report->header->agings ?? [] as $idx => $aging)
                @if($detail->aging_index == $idx)
                <td>{{ $detail->temperature ?? '-' }}</td>
                <td>{{ $detail->sensory ?? '-' }}</td>
                @else
                <td>-</td>
                <td>-</td>
                @endif
                @endforeach
            </tr>
            @endforeach

            {{-- Start aging --}}
            <tr>
                <td colspan="2">Start aging</td>
                @foreach($report->header->agings ?? [] as $aging)
                <td colspan="2">{{ $aging->start_aging ?? '-' }}</td>
                @endforeach
            </tr>
            {{-- Finish aging --}}
            <tr>
                <td colspan="2">Finish aging</td>
                @foreach($report->header->agings ?? [] as $aging)
                <td colspan="2">{{ $aging->finish_aging ?? '-' }}</td>
                @endforeach
            </tr>
            {{-- Hasil emulsi (sensory) --}}
            <tr>
                <td colspan="2">Hasil emulsi (sensory)</td>
                @foreach($report->header->agings ?? [] as $aging)
                <td colspan="2">{{ $aging->emulsion_result ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td colspan="{{ 2 + (count($report->header->agings ?? []) * 2) }}"
                    style="text-align: right; border: none;">
                    QM 45 / 01
                </td>
            </tr>
        </tbody>
    </table>

    <p>Keterangan : √ : OK X : tidak OK</p>

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