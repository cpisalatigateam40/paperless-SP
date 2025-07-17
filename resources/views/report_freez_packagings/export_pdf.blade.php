<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Fessman Cooking</title>
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

        .no-border {
            border: none !important;
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
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 30%;">
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
                                <img src="{{ $base64 ?? '' }}" style="width: 50px;">
                            </td>
                            <td class="no-border" style="padding-left: 10px;">
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

    <h3 style="text-align: center;">VERIFIKASI PEMBEKUAN IQF & PENGEMASAN KARTON BOX</h3>

    <table style="border: none;">
        <tr style="border: none;">
            <td style="border: none;">Hari/Tanggal: <span
                    class="underline">{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}</span>
            </td>
            <td style="border: none;">Shift: <span class="underline">{{ $report->shift }}</span></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Waktu Pemeriksaan</th>
                <th rowspan="2">Nama Produk</th>
                <th rowspan="2">Kode Produksi</th>
                <th rowspan="2">Best Before</th>
                <th colspan="2">Suhu Produk</th>
                <th colspan="2">Suhu IQF</th>
                <th colspan="2">Lama Pembekuan</th>
                <th rowspan="2">Kode Karton</th>
                <th colspan="2">Isi tiap Karton</th>
                <th colspan="2">Berat/Karton (kg)</th>
            </tr>
            <tr>
                <th>Awal</th>
                <th>Akhir</th>
                <th>Room</th>
                <th>Suction</th>
                <th>Display</th>
                <th>Aktual</th>
                <th>Bag</th>
                <th>Binded</th>
                <th>Standar</th>
                <th>Aktual</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
                <tr>
                    <td>
                        {{ $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('H:i') : '' }} -
                        {{ $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('H:i') : '' }}
                    </td>
                    <td>{{ $detail->product->product_name ?? '-' }}</td>
                    <td>{{ $detail->production_code }}</td>
                    <td>{{ $detail->best_before }}</td>

                    {{-- Freezing --}}
                    <td>{{ $detail->freezing->start_product_temp ?? '' }}</td>
                    <td>{{ $detail->freezing->end_product_temp ?? '' }}</td>
                    <td>{{ $detail->freezing->iqf_room_temp ?? '' }}</td>
                    <td>{{ $detail->freezing->iqf_suction_temp ?? '' }}</td>
                    <td>{{ $detail->freezing->freezing_time_display ?? '' }}</td>
                    <td>{{ $detail->freezing->freezing_time_actual ?? '' }}</td>

                    {{-- Kartoning --}}
                    <td>{{ $detail->kartoning->carton_code ?? '' }}</td>
                    <td>{{ $detail->kartoning->content_bag ?? '' }}</td>
                    <td>{{ $detail->kartoning->content_binded ?? '' }}</td>
                    <td>{{ $detail->kartoning->carton_weight_standard ?? '' }}</td>
                    <td>{{ $detail->kartoning->carton_weight_actual ?? '' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="15" style="text-align: right; border: none;">QM 39 / 02</td>
            </tr>
        </tbody>
    </table>

    <br><br>

    {{-- Footer QR --}}
    <table style="width: 100%; border: none;">
        <tr>
            <td style="text-align: center; border: none; width: 33%;">
                Dibuat oleh:<br><br>
                <img src="{{ $createdQr }}" width="80"><br>
                <strong>{{ $report->created_by }}</strong><br>QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh:<br><br>
                <div style="height: 50px;"></div>
                <strong>{{ $report->known_by }}</strong><br>SPV/Foreman
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="80"><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height: 120px;"></div>
                    <strong>-</strong>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>
</body>

</html>