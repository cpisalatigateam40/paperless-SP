<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export PDF</title>
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
        size: 210mm 330mm;
    }

    ul {
        margin: unset;
        padding: .2rem;
    }

    li {
        list-style-type: none;
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

    <h3 class="mb-2 text-center">VERIFIKASI PROSES PRODUKSI</h3>

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

    @foreach ($report->detail as $detail)
    <table>
        <tr>
            <th colspan="2">NAMA PRODUK</th>
            <td colspan="4">{{ $detail->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">KODE PRODUKSI</th>
            <td colspan="4">{{ $detail->production_code ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">NOMOR FORMULA</th>
            <td colspan="4">{{ $detail->formula->formula_name ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="6">WAKTU MIXING: {{ $detail->mixing_time ?? '-' }}</th>
        </tr>

        {{-- A. BAHAN BAKU --}}
        <tr class="table-secondary">
            <td colspan="6">A. BAHAN BAKU</td>
        </tr>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Berat (kg)</th>
            <th>Sensorik</th>
            <th>Kode Produksi</th>
            <th>Suhu (℃)</th>
        </tr>
        @php $i = 1; @endphp
        @foreach ($detail->items->filter(fn($item) => $item->formulation?->raw_material_uuid) as $item)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $item->formulation->rawMaterial->material_name ?? '-' }}</td>
            <td>{{ $item->actual_weight }}</td>
            <td>{{ $item->sensory }}</td>
            <td>{{ $item->prod_code }}</td>
            <td>{{ $item->temperature }}</td>
        </tr>
        @endforeach

        {{-- B. PREMIX --}}
        <tr class="table-secondary">
            <td colspan="6">B. PREMIX / BAHAN TAMBAHAN</td>
        </tr>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Berat (kg)</th>
            <th>Sensorik</th>
            <th>Kode Produksi</th>
            <th>Suhu (℃)</th>
        </tr>
        @php $j = 1; @endphp
        @foreach ($detail->items->filter(fn($item) => $item->formulation?->premix_uuid) as $item)
        <tr>
            <td>{{ $j++ }}</td>
            <td>{{ $item->formulation->premix->name ?? '-' }}</td>
            <td>{{ $item->actual_weight }}</td>
            <td>{{ $item->sensory }}</td>
            <td>{{ $item->prod_code }}</td>
            <td>{{ $item->temperature }}</td>
        </tr>
        @endforeach

        {{-- REWORK --}}
        <tr>
            <th colspan="2">REWORK (kg/%)</th>
            <td colspan="4">{{ $detail->rework_kg ?? '-' }} / {{ $detail->rework_percent ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">TOTAL BAHAN (kg)</th>
            <td colspan="4">{{ $detail->total_material ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Sensori Homogenitas</th>
            <td colspan="4">{{ $detail->sensory_homogenity ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Sensori Kekentalan</th>
            <td colspan="4">{{ $detail->sensory_stiffness ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Sensori Aroma</th>
            <td colspan="4">{{ $detail->sensory_aroma ?? '-' }}</td>
        </tr>

        {{-- EMULSIFYING --}}
        <tr class="table-secondary">
            <td colspan="6">C. EMULSIFYING</td>
        </tr>
        <tr>
            <th colspan="2">Standar suhu adonan (℃)</th>
            <td colspan="4">{{ $detail->emulsifying->standard_mixture_temp ?? '14 ± 2' }}</td>
        </tr>
        <tr>
            <th colspan="2">Aktual suhu adonan (℃)</th>
            <td colspan="4">
                {{ $detail->emulsifying->actual_mixture_temp_1 ?? '-' }} /
                {{ $detail->emulsifying->actual_mixture_temp_2 ?? '-' }} /
                {{ $detail->emulsifying->actual_mixture_temp_3 ?? '-' }}
            </td>
        </tr>
        <tr>
            <th colspan="2">Rata-rata suhu adonan (℃)</th>
            <td colspan="4">{{ $detail->emulsifying->average_mixture_temp ?? '-' }}</td>
        </tr>

        {{-- SENSORIK --}}
        <tr class="table-secondary">
            <td colspan="6">D. SENSORIK</td>
        </tr>
        <tr>
            <th colspan="2">Homogenitas</th>
            <td colspan="4">{{ $detail->sensoric->homogeneous ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Kekentalan</th>
            <td colspan="4">{{ $detail->sensoric->stiffness ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Aroma</th>
            <td colspan="4">{{ $detail->sensoric->aroma ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Benda Asing</th>
            <td colspan="4">{{ $detail->sensoric->foreign_object ?? '-' }}</td>
        </tr>

        {{-- TUMBLING --}}
        <tr class="table-secondary">
            <td colspan="6">E. TUMBLING</td>
        </tr>
        <tr>
            <th colspan="2">Proses Tumbling</th>
            <td colspan="4">{{ $detail->tumbling->tumbling_process ?? '-' }}</td>
        </tr>

        {{-- AGING --}}
        <tr class="table-secondary">
            <td colspan="6">F. AGING</td>
        </tr>
        <tr>
            <th colspan="2">Proses Aging</th>
            <td colspan="4">{{ $detail->aging->aging_process ?? '-' }}</td>
        </tr>
        <tr>
            <th colspan="2">Hasil Stuffing</th>
            <td colspan="4">{{ $detail->aging->stuffing_result ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: right; border: none;">QM 08 / 02</td>
        </tr>
    </table>
    @endforeach

    <p>Keterangan: &#10003; = OK, &#10007; = Tidak OK</p>

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