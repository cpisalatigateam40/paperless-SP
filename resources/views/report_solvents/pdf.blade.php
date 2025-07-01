<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Verifikasi Pembuatan Larutan Cleaning dan Sanitasi</title>
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
        size: 210mm 330mm;
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

    <h3 class="mb-2 text-center">VERIFIKASI PEMBUATAN LARUTAN CLEANING DAN SANITASI</h3>

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

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="align-middle">No.</th>
                <th rowspan="2" class="align-middle">Nama Bahan</th>
                <th rowspan="2" class="align-middle">Kadar Yang Diinginkan</th>
                <th colspan="2" class="align-middle">Verifikasi Formulasi</th>
                <th rowspan="2" class="align-middle">Keterangan</th>
                <th rowspan="2" class="align-middle">Hasil Verifikasi</th>
                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
            </tr>
            <tr>
                <th class="align-middle">Volume Bahan (mL)</th>
                <th class="align-middle">Volume Larutan (mL)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-start">{{ $detail->solvent->name ?? '-' }}</td>
                <td>{{ $detail->solvent->concentration ?? '-' }}</td>
                <td>{{ $detail->solvent->volume_material ?? '-' }}</td>
                <td>{{ $detail->solvent->volume_solvent ?? '-' }}</td>
                <td class="text-start">{{ $detail->solvent->application_area ?? '-' }}</td>
                <td style="text-align: center">{!! $detail->verification_result == '1' ? '✓' :
                    ($detail->verification_result == '0' ? 'x' : '-') !!}</td>
                <td>{{ $detail->corrective_action ?? '-' }}</td>
                <td>
                    <ul class="mb-0" style="padding-left: 1rem; text-align: left;">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi:
                            {{ $detail->reverification_action == '1' ? 'OK' : ($detail->reverification_action == '0' ? 'Tidak OK' : '-') }}
                            Tindakan Koreksi: {{ $detail->corrective_action ?? '-' }}
                        </li>

                        @foreach($detail->followups as $index => $followup)
                        <li class="mt-1">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi:
                            {{ $followup->verification == '1' ? 'OK' : ($followup->verification == '0' ? 'Tidak OK' : '-') }}<br>
                            Keterangan: {{ $followup->notes ?? '-' }}<br>
                            Tindakan Koreksi: {{ $followup->corrective_action ?? '-' }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="9" style="text-align: right; border: none;">QM 44 / 01</td>
            </tr>
        </tbody>
    </table>


    <p style="margin-top: 2rem;">
        <strong>Keterangan:</strong><br>
        ✓ = Perbandingan formulasi sesuai. Pelarut yang digunakan adalah pelarut AR.<br>
        x = Perbandingan formulasi tidak sesuai.
    </p>

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