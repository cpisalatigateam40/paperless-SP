<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Packaging Verif PDF</title>
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

    <h3 class="mb-2 text-center">VERIFIKASI PEMERIKSAAN KEMASAN PLASTIK</h3>

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

    <table class="table table-bordered table-sm text-center align-middle mb-4">
        <tr>
            <th rowspan="2">Jam</th>
            <th rowspan="2">Produk</th>
            <th rowspan="2">Kode Produksi</th>
            <th rowspan="2">Expired date</th>
            <th colspan="2">In cutting</th>
            <th colspan="2">Proses Pengemasan</th>
            <th colspan="2">Hasil Sealing</th>
            <th rowspan="2">Isi Per-Pack</th>
            <th colspan="2">Berat Produk Per Plastik (gr)</th>
        </tr>
        <tr>
            <th>Manual</th>
            <th>Mesin</th>
            <th>Thermoformer</th>
            <th>Manual</th>
            <th>Kondisi Seal</th>
            <th>Vacum</th>
            <th>Standar</th>
            <th>Aktual</th>
        </tr>

        @foreach($report->details as $d)
        @php $checklist = $d->checklist; @endphp

        @for($i=1; $i<=5; $i++) <tr>
            @if($i==1)
            <td rowspan="5">{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
            <td rowspan="5">{{ $d->product->product_name ?? '-' }}</td>
            <td rowspan="5">{{ $d->production_code }}</td>
            <td rowspan="5">{{ $d->expired_date }}</td>

            {{-- In cutting manual & mesin sama, rowspan --}}
            <td rowspan="5">{{ $checklist?->in_cutting_manual_1 ?? '-' }}</td>
            <td rowspan="5">{{ $checklist?->in_cutting_machine_1 ?? '-' }}</td>

            {{-- Packaging thermoformer & manual, rowspan --}}
            <td rowspan="5">{{ $checklist?->packaging_thermoformer_1 ?? '-' }}</td>
            <td rowspan="5">{{ $checklist?->packaging_manual_1 ?? '-' }}</td>
            @endif

            {{-- Hasil sealing & isi per-pack, per baris --}}
            <td>{{ $checklist?->{'sealing_condition_'.$i} ?? '-' }}</td>
            <td>{{ $checklist?->{'sealing_vacuum_'.$i} ?? '-' }}</td>
            <td>{{ $checklist?->{'content_per_pack_'.$i} ?? '-' }}</td>

            @if($i==1)
            {{-- Standard weight sekali saja --}}
            <td rowspan="5">{{ $checklist?->standard_weight ?? '-' }}</td>
            @endif

            {{-- Actual weight --}}
            <td>{{ $checklist?->{'actual_weight_'.$i} ?? '-' }}</td>
            </tr>
            @endfor

            {{-- Optional QC & KR --}}
            <!--
    <tr>
        <td colspan="3" class="text-start">QC: {{ $d->qc_verif ?? '-' }}</td>
        <td colspan="3" class="text-start">KR: {{ $d->kr_verif ?? '-' }}</td>
        <td colspan="7"></td>
    </tr>
    -->
            @endforeach

            <tr>
                <td colspan="13" class="text-end" style="border: none;">QM 05 / 01</td>
            </tr>
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