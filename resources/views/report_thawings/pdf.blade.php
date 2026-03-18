<!DOCTYPE html>
<html>

<head>
    <title>Laporan Thawing Bahan Baku</title>

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

    .no-border {
        border: none !important;
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

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 30%; vertical-align: middle;">
                    <table style="border: none;">
                        <tr>
                            <td class="no-border" style="width: 50px;">

                                @php
                                $path = public_path('storage/image/logo.png');

                                if (file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp

                                <img src="{{ $base64 ?? '' }}" style="width:50px">

                            </td>

                            <td class="no-border" style="padding-left:10px;">
                                <div style="font-size:9px;font-weight:bold;line-height:1.2;">
                                    CHAROEN<br>
                                    POKPHAND<br>
                                    INDONESIA PT.<br>
                                    Food Division
                                </div>
                            </td>

                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>


    <h3 class="text-center">PEMERIKSAAN PROSES THAWING</h3>


    <table style="border:none;">
        <tr style="border:none;">

            <td class="no-border">
                Hari/Tanggal :
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>

            <td class="no-border">
                Shift :
                <span style="text-decoration: underline;">
                    {{ $report->shift }}
                </span>
            </td>

        </tr>
    </table>


    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>Waktu Thawing Awal</th>
                <th>Waktu Thawing Akhir</th>
                <th>Kondisi Awal Kemasan RM</th>
                <th>Nama Bahan Baku</th>
                <th>Kode Produksi</th>
                <th>Jumlah</th>
                <th>Kondisi Ruang</th>
                <th>Waktu Pemeriksaan</th>
                <th>Suhu Ruang (°C)</th>
                <th>Suhu Air Thawing (°C)</th>
                <th>Suhu Produk (°C)</th>
                <th>Kondisi Produk</th>

            </tr>

        </thead>


        <tbody>

            @foreach($report->details as $i => $detail)

            <tr>

                <td class="text-center">
                    {{ $i+1 }}
                </td>

                <td>
                    {{ $detail->start_thawing_time ?? '-' }}
                </td>

                <td>
                    {{ $detail->end_thawing_time ?? '-' }}
                </td>

                <td>
                    {{ ucfirst($detail->package_condition) ?? '-' }}
                </td>

                <td>
                    {{ $detail->rawMaterial->material_name ?? '-' }}
                </td>

                <td>
                    {{ $detail->production_code ?? '-' }}
                </td>

                <td>
                    {{ $detail->qty ?? '-' }}
                </td>

                <td>
                    {{ ucfirst($detail->room_condition) ?? '-' }}
                </td>

                <td>
                    {{ $detail->inspection_time ?? '-' }}
                </td>

                <td>
                    {{ $detail->room_temp ? $detail->room_temp.' °C' : '-' }}
                </td>

                <td>
                    {{ $detail->water_temp ? $detail->water_temp.' °C' : '-' }}
                </td>

                <td>
                    {{ $detail->product_temp ? $detail->product_temp.' °C' : '-' }}
                </td>

                <td>
                    {{ ucfirst($detail->product_condition) ?? '-' }}
                </td>

            </tr>

            @endforeach

            <!-- <td colspan="13" style="text-align:right;border:none;">
                QM 14 / 00
            </td> -->

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