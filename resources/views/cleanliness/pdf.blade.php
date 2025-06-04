<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form PDF - Kebersihan Ruangan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        .text-center { text-align: center; }
        .signature-box { height: 60px; border-bottom: 1px solid #000; margin-top: 30px; width: 60%; }

        .no-border {
            border: none !important;
        }

        .mb-2 {
            margin-bottom: 2rem;
        }

        .mb-3 {
            margin-bottom: 3rem;
        }

        .mb-4 {
            margin-bottom: 4rem;
        }

        .underline {
            text-decoration: underline;
        }

        th {
            font-weight: normal;
            text-align: center
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
            margin-top: 100px;
            size: 210mm 330mm;
            margin-header: 10mm;
        }

        body {
            margin-top: 30px;
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

    <h3 class="mb-2 text-center">KONDISI RUANG PENYIMPANAN BAHAN BAKU DAN PENUNJANG</h3>

    <table>
        <thead>
            <tr>
                <th>Jam</th>
                <th>No</th>
                <th>Item</th>
                <th>Kondisi</th>
                <th>Keterangan</th>
                <th>Tindakan Koreksi</th>
                <th>Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->details as $detail)
                @foreach($detail->items as $i => $item)
                    <tr>
                        @if($i === 0)
                            <td rowspan="4">{{ $detail->inspection_hour }}</td>
                        @endif
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->item }}</td>
                        <td>{{ $item->condition }}</td>
                        <td>{{ $item->notes }}</td>
                        <td>{{ $item->corrective_action }}</td>
                        <td>{{ $item->verification == 1 ? 'OK' : 'Tidak OK' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <p><strong>Keterangan:</strong></p>
    <ul style="padding-left: 20px;">
        <li>1. Tertata rapi</li>
        <li>2. Penempatan sesuai tagging dan jenis allergen</li>
        <li>3. Bersih dan bebas dari kontaminan</li>
        <li>4. Tidak tertata rapi</li>
        <li>5. Penempatan tidak sesuai tagging dan jenis allergen</li>
        <li>6. Tidak bersih / ada kontaminan</li>
    </ul>

    <br><br>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="text-align: center; border: none;">
                Diperiksa oleh,<br><br><div style="height: 50px;"></div>
                <strong>{{ $report->created_by }}</strong><br>QC Inspector
            </td>
            <td style="text-align: center; border: none;">
                Diketahui oleh,<br><br><div style="height: 50px;"></div>
                <strong>{{ $report->known_by }}</strong><br>SPV/Foreman /Lady Produksi
            </td>
            <td style="text-align: center; border: none;">
                Disetujui oleh,<br><br><div style="height: 50px;"></div>
                <strong>{{ $report->approved_by }}</strong><br>SPV/ Forelady QC
            </td>
        </tr>
    </table>

</body>
</html>
