<!DOCTYPE html>
<html>

<head>
    <title>Laporan Verifikasi Berat Stuffer</title>
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

    <h3 class="mb-2 text-center">VERIFIKASI BERAT STUFFER</h3>

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

    @php
    $details = $report->details;
    @endphp
    <table class="table table-bordered table-sm text-center align-middle mb-4">
        {{-- Heading baris nama produk --}}
        <tr>
            <th class="text-start">Nama Produk</th>
            @foreach ($details as $d)
            <th>{{ $d->product->product_name ?? '-' }}</th>
            @endforeach
        </tr>
        <tr>
            <th class="text-start">Kode Produksi</th>
            @foreach ($details as $d)
            <td>{{ $d->production_code }}</td>
            @endforeach
        </tr>
        <tr>
            <th class="text-start">Waktu Proses</th>
            @foreach ($details as $d)
            <td>{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
            @endforeach
        </tr>
        <tr>
            <th class="text-start">Mesin Stuffer</th>
            @foreach ($details as $d)
            <td>{{ $d->townsend ? 'Townsend' : 'Hitech' }}</td>
            @endforeach
        </tr>

        @php
        $labels = [
        'Kecepatan Stuffer (rpm)' => 'speed',
        'Ukuran Casing<br><small>(Aktual Panjang, Diameter)</small>' => 'casing',
        'Standar Berat (gr)' => 'standard',
        'Berat Aktual (gr)' => 'actual_weight',
        'Rata-rata Berat Aktual (gr)' => 'avg',
        'Standar Panjang' => 'standard_long',
        'Panjang Aktual' => 'actual_long',
        'Rata-rata Panjang Aktual' => 'avg_long',
        'Catatan' => 'notes',
        ];
        @endphp

        @foreach ($labels as $label => $key)
        <tr>
            <td class="text-start">{!! $label !!}</td>
            @foreach ($details as $d)
            @php
            // pilih mesin sesuai data yang ada
            $stuffer = $d->townsend ?? $d->hitech;
            $case = $d->cases->first();
            $weight = $d->weights->first();
            @endphp

            @switch($key)
            @case('speed')
            <td>{{ $stuffer?->stuffer_speed ?? '-' }}</td>
            @break

            @case('casing')
            <td>{{ $case?->actual_case_1 ?? '-' }} / {{ $case?->actual_case_2 ?? '-' }}</td>
            @break

            @case('standard')
            <td>{{ $d->weight_standard ?? '-' }}</td>
            @break

            @case('actual_weight')
            <td>
                @if($d->weights->count() > 0)
                {{ $d->weights->pluck('actual_weight')->filter()->implode(' / ') }}
                @else
                -
                @endif
            </td>
            @break

            @case('avg')
            <td>{{ $stuffer?->avg_weight ?? '-' }}</td>
            @break

            @case('standard_long')
            <td>{{ $d->long_standard ?? '-' }}</td>
            @break

            @case('actual_long')
            <td>
                @if($d->weights->count() > 0)
                {{ $d->weights->pluck('actual_long')->filter()->implode(' / ') }}
                @else
                -
                @endif
            </td>
            @break

            @case('avg_long')
            <td>{{ $stuffer?->avg_long ?? '-' }}</td>
            @break

            @case('notes')
            <td>{{ $stuffer?->notes ?? '-' }}</td>
            @break
            @endswitch
            @endforeach
        </tr>
        @endforeach
    </table>

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