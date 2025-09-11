<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pemeriksaan Pembuatan Kulit Siomay, Gioza & Mandu</title>
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

    <h3 class="mb-2 text-center">PEMERIKSAAN PEMASAKAN PRODUK DI STEAM KETTLE</h3>

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

    <table class="table table-bordered table-sm align-middle text-center">
        {{-- Header Informasi --}}
        <tr>
            <th class="text-start">Nama Produk</th>
            <td colspan="15" class="text-start" style="text-align: start !important;">
                {{ $report->product->product_name }}</td>
        </tr>

        <tr>
            <th class="text-start">Kode Produksi</th>
            <td colspan="15" class="text-start" style="text-align: start !important;">
                {{ $report->production_code }}</td>
        </tr>

        <tr>
            <th class="text-start">Waktu (Start - Stop)</th>
            <td colspan="15" class="text-start" style="text-align: start !important;">
                {{ $report->start_time }} -
                {{ $report->end_time }}</td>
        </tr>

        {{-- Header Kolom Utama --}}
        <tr>
            <th rowspan="2">Pukul</th>
            <th rowspan="2">Tahapan Proses</th>
            <th colspan="3">Bahan Baku</th>
            <th colspan="6">Parameter Pemasakan</th>
            <th colspan="4">Produk Organoleptik</th>
            <th rowspan="2">Catatan</th>
        </tr>
        <tr>
            <th>Jenis Bahan</th>
            <th>Jumlah (Kg)</th>
            <th>Sensori</th>

            <th>Lama Proses (menit)</th>
            <th>Mixing Paddle On</th>
            <th>Mixing Paddle Off</th>
            <th>Pressure (Bar)</th>
            <th>Target Temp (&#176;C)</th>
            <th>Actual Temp (&#176;C)</th>

            <th>Warna</th>
            <th>Aroma</th>
            <th>Rasa</th>
            <th>Tekstur</th>
        </tr>

        {{-- Isi Data --}}
        @foreach($report->details as $d)
        {{-- baris utama per proses --}}
        <tr>
            <td>{{ $d->time }}</td>
            <td>{{ $d->process_step }}</td>

            {{-- bahan baku ditaruh di cell bersarang --}}
            <td colspan="3" class="p-0">
                <table class="table table-sm mb-0 table-borderless">
                    @foreach($d->rawMaterials as $rm)
                    <tr>
                        <td style="text-align: start !important; border: none;">
                            {{ $rm->rawMaterial->material_name ?? '-' }}</td>
                        <td style="text-align: start !important; border: none;">{{ $rm->amount }}</td>
                        <td style="text-align: start !important; border: none;">{{ $rm->sensory }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>

            <td>{{ $d->duration }}</td>
            <td>{{ $d->mixing_paddle_on ? 'V' : '-' }}</td>
            <td>{{ $d->mixing_paddle_off ? 'V' : '-' }}</td>
            <td>{{ $d->pressure }}</td>
            <td>{{ $d->target_temperature }}</td>
            <td>{{ $d->actual_temperature }}</td>

            <td>{{ $d->color }}</td>
            <td>{{ $d->aroma }}</td>
            <td>{{ $d->taste }}</td>
            <td>{{ $d->texture }}</td>

            <td>{{ $d->notes }}</td>
        </tr>
        @endforeach
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