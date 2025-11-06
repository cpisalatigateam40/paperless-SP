<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Waterbath</title>
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

    <h3 class="mb-2 text-center">PENGECEKAN PASTEURISASI PRODUK RTG WATERBATH</h3>

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

    <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th>Produk</th>
                <th>Gramase</th>
                <th>Batch</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Pasteurisasi</th>
                <th>Cooling Shock</th>
                <th>Dripping</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
            $max = max(
            $report->details->count(),
            $report->pasteurisasi->count(),
            $report->coolingShocks->count(),
            $report->drippings->count()
            );
            @endphp

            @for($i = 0; $i < $max; $i++) <tr>
                {{-- Detail Produk --}}
                <td>{{ $report->details[$i]->product->product_name ?? '-' }}</td>
                <td>{{ $report->details[$i]->product->nett_weight ?? '-' }} g</td>
                <td>{{ $report->details[$i]->batch_code ?? '-' }}</td>
                <td>{{ $report->details[$i]->amount ?? '-' }}</td>
                <td>{{ $report->details[$i]->unit ?? '-' }}</td>

                {{-- Pasteurisasi --}}
                <td>
                    @if(isset($report->pasteurisasi[$i]))
                    Suhu Awal Produk: {{ $report->pasteurisasi[$i]->initial_product_temp }}
                    <br>
                    Suhu Awal Air: {{ $report->pasteurisasi[$i]->initial_water_temp }} <br>
                    Start Pasteurisasi: {{ $report->pasteurisasi[$i]->start_time_pasteur }}
                    <br>
                    Stop Pasteurisasi: {{ $report->pasteurisasi[$i]->stop_time_pasteur }}
                    <br>
                    Suhu air setelah produk dimasukkan panel:
                    {{ $report->pasteurisasi[$i]->water_temp_after_input_panel }} <br>
                    Suhu air setelah produk dimasukkan aktual:
                    {{ $report->pasteurisasi[$i]->water_temp_after_input_actual }} <br>
                    Suhu air setting:
                    {{ $report->pasteurisasi[$i]->water_temp_setting }} <br>
                    Suhu air aktual:
                    {{ $report->pasteurisasi[$i]->water_temp_actual }} <br>
                    Suhu akhir air:
                    {{ $report->pasteurisasi[$i]->water_temp_final }} <br>
                    Suhu akhir produk:
                    {{ $report->pasteurisasi[$i]->product_temp_final }} <br>
                    @endif
                </td>

                {{-- Cooling Shock --}}
                <td>
                    @if(isset($report->coolingShocks[$i]))
                    Suhu Awal Air: {{ $report->coolingShocks[$i]->initial_water_temp }} <br>
                    Start Pasteurisasi: {{ $report->coolingShocks[$i]->start_time_pasteur }}
                    <br>
                    Stop Pasteurisasi: {{ $report->coolingShocks[$i]->stop_time_pasteur }}
                    <br>
                    Suhu air setting: {{ $report->coolingShocks[$i]->water_temp_setting }}
                    <br>
                    Suhu air aktual: {{ $report->coolingShocks[$i]->water_temp_actual }}
                    <br>
                    Suhu akhir air: {{ $report->coolingShocks[$i]->water_temp_final }}
                    <br>
                    Suhu akhir produk: {{ $report->coolingShocks[$i]->product_temp_final }}
                    <br>

                    @endif
                </td>

                {{-- Dripping --}}
                <td>
                    @if(isset($report->drippings[$i]))
                    Start Pasteurisasi: {{ $report->drippings[$i]->start_time_pasteur }}
                    <br>
                    Stop Pasteurisasi: {{ $report->drippings[$i]->stop_time_pasteur }} <br>
                    Suhu Zona Panas: {{ $report->drippings[$i]->hot_zone_temperature }} <br>
                    Suhu Zona Dingin: {{ $report->drippings[$i]->cold_zone_temperature }}
                    <br>
                    Suhu Akhir Produk: {{ $report->drippings[$i]->product_temp_final }}
                    @endif
                </td>

                <td>{{ $report->details[$i]->note ?? '-' }}</td>
                </tr>
                @endfor
        </tbody>
    </table>

    <br><br>
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