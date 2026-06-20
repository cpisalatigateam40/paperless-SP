<!DOCTYPE html>
<html>

<head>
    <title>Pemeriksaan Kebersihan Magnet Trap</title>

    <style>
        @font-face {
            font-family: "DejaVu Sans";
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format("truetype");
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
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
            padding: 3px;
            vertical-align: middle;
        }

        th {
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .no-border {
            border: none !important;
        }

        .mb-2 {
            margin-bottom: 1rem;
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
            size: A4 landscape;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
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

                                        $base64 =
                                            'data:image/' .
                                            $type .
                                            ';base64,' .
                                            base64_encode($data);
                                    }
                                @endphp

                                <img src="{{ $base64 ?? '' }}"
                                    style="width: 50px;">
                            </td>

                            <td class="no-border"
                                style="vertical-align: middle; padding-left: 10px;">

                                <div style="font-size: 9px;
                                            font-weight: bold;
                                            line-height: 1.2;">

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

    <h3 class="text-center mb-2">
        PEMERIKSAAN KEBERSIHAN MAGNET TRAP
    </h3>

    {{-- INFORMASI HEADER --}}
    <table style="border: none;">
        <tr style="border: none;">

            <td style="border: none;">
                Hari/Tanggal :
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)
                        ->translatedFormat('l, d/m/Y') }}
                </span>
            </td>

            <td style="border: none;">
                Shift :
                <span style="text-decoration: underline;">
                    {{ $report->shift }}
                </span>
            </td>

            <td style="border: none;">
                Area :
                <span style="text-decoration: underline;">
                    {{ $report->area->name ?? '-' }}
                </span>
            </td>

        </tr>
    </table>

    {{-- TABEL DETAIL --}}
    <table>

        <thead>

            <tr>
                <th rowspan="2" style="width:4%;">No</th>

                <th rowspan="2" style="width:18%;">
                    Nama Produk
                </th>

                <th rowspan="2" style="width:6%;">
                    Jam
                </th>

                <th rowspan="2" style="width:10%;">
                    Magnet Trap I
                </th>

                <th rowspan="2" style="width:10%;">
                    Magnet Trap II
                </th>

                <th rowspan="2" style="width:14%;">
                    Jenis Temuan
                </th>

                <th colspan="2" style="width:10%;">
                    Kondisi
                </th>

                <th rowspan="2" style="width:14%;">
                    Keterangan
                </th>

                <th rowspan="2" style="width:14%;">
                    Tindakan Koreksi
                </th>
            </tr>

            <tr>
                <th style="width:5%;">
                    Bersih
                </th>

                <th style="width:5%;">
                    Tidak Bersih
                </th>
            </tr>

        </thead>

        <tbody>

            @foreach($report->details as $i => $detail)

            <tr>

                <td class="text-center">
                    {{ $i + 1 }}
                </td>

                <td>
                    {{ $detail->product->product_name ?? '-' }}
                </td>

                <td class="text-center">
                    {{ $detail->time
                        ? \Illuminate\Support\Str::substr($detail->time,0,5)
                        : '-' }}
                </td>

                <td>
                    {{ $detail->mt_1 ?? '-' }}
                </td>

                <td>
                    {{ $detail->mt_2 ?? '-' }}
                </td>

                <td>
                    {{ $detail->finding_type ?? '-' }}
                </td>

                <td class="text-center">
                    {{ $detail->condition == 'Bersih' ? '✓' : '' }}
                </td>

                <td class="text-center">
                    {{ $detail->condition == 'Tidak Bersih' ? '✓' : '' }}
                </td>

                <td>
                    {{ $detail->note ?? '-' }}
                </td>

                <td>
                    {{ $detail->corrective_action ?? '-' }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    {{-- TTD --}}
    <table style="width:100%; border:none; margin-top: 3rem;">
        <tr style="border:none;">

            <td style="text-align:center; border:none; width:33%;">

                Dibuat oleh:<br><br>

                <img src="{{ $createdQr }}"
                    width="80"
                    style="margin: 10px 0;">

                <br>

                <strong>
                    {{ $report->created_by }}
                </strong>

            </td>

            <td style="text-align:center; border:none; width:33%;">

                Diketahui oleh:<br><br>

                @if($report->known_by)

                <img src="{{ $knownQr }}"
                    width="80"
                    style="margin: 10px 0;">

                <br>

                <strong>
                    {{ $report->known_by }}
                </strong>

                @else

                <div style="height:120px;"></div>

                <strong>-</strong>

                @endif

            </td>

            <td style="text-align:center; border:none; width:33%;">

                Disetujui oleh:<br><br>

                @if($report->approved_by)

                <img src="{{ $approvedQr }}"
                    width="80"
                    style="margin: 10px 0;">

                <br>

                <strong>
                    {{ $report->approved_by }}
                </strong>

                @else

                <div style="height:120px;"></div>

                <strong>-</strong>

                @endif

            </td>

        </tr>
    </table>

</body>

</html>