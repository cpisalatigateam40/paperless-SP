<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeriksaan Pra Operasi</title>
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
        vertical-align: top;
    }

    th {
        text-align: center;
        font-weight: normal;
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

    <h3 class="mb-2 text-center">Pemeriksaan Pra Operasi Produk</h3>

    <table style="width: 100%; margin-bottom: 10px; border: none;">
        <tr>
            <td style="width: 50%; border: none;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Hari/Tanggal</strong></td>
                        <span style="text-decoration: underline;">&nbsp;:
                            {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                        </span>
                    </tr>
                    <tr>
                        <td style="border: none;"><strong>Produk yang akan diproduksi</strong></td>
                        <td style="border: none;">: {{ $report->product->product_name ?? '__________' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;"><strong>Kode Produksi</strong></td>
                        <td style="border: none;">: {{ $report->production_code }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; border: none; vertical-align: top; text-align: end;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none;"><strong>Shift</strong></td>
                        <td style="border: none;">: {{ $report->shift }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">PARAMETER PENGECEKAN</th>
                <th colspan="4">PENILAIAN KONDISI BAHAN/PERALATAN</th>
                <th rowspan="2">TINDAKAN KOREKSI</th>
                <th rowspan="2">VERIFIKASI</th>
            </tr>
            <tr>
                <th>1 / 2</th>
                <th>3 / 4</th>
                <th>5 / 6</th>
                <th>7 / 8</th>
            </tr>
        </thead>
        <tbody>
            {{-- BAHAN --}}
            <tr>
                <td colspan="8"><strong>BAHAN BAKU & PENUNJANG</strong></td>
            </tr>
            @foreach ($report->materials as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item }}</td>
                <td class="text-center">{{ in_array($item->condition, [1, 2]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [3, 4]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [5, 6]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [7, 8]) ? $item->condition : '' }}</td>
                <td>{{ $item->corrective_action }}</td>
                <td>{{ $item->verification }}</td>
            </tr>
            @endforeach

            {{-- KEMASAN --}}
            <tr>
                <td colspan="8"><strong>KEMASAN</strong></td>
            </tr>
            @foreach ($report->packagings as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item }}</td>
                <td class="text-center">{{ in_array($item->condition, [1, 2]) ? $item->condition : '' }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $item->corrective_action }}</td>
                <td>{{ $item->verification }}</td>
            </tr>
            @endforeach

            {{-- PERALATAN --}}
            <tr>
                <td colspan="8"><strong>MESIN & PERALATAN</strong></td>
            </tr>
            @foreach ($report->equipments as $i => $eq)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $eq->equipment->name ?? '-' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [1, 2]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [3, 4]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [5, 6]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [7, 8]) ? $eq->condition : '' }}</td>
                <td>{{ $eq->corrective_action }}</td>
                <td>{{ $eq->verification }}</td>
            </tr>
            @endforeach

            {{-- RUANGAN --}}
            <tr>
                <td colspan="8"><strong>KONDISI RUANGAN</strong></td>
            </tr>
            @foreach ($report->rooms as $i => $room)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $room->section->section_name ?? '-' }}</td>
                <td></td>
                <td class="text-center">{{ in_array($room->condition, [3, 4]) ? $room->condition : '' }}</td>
                <td class="text-center">{{ in_array($room->condition, [5, 6]) ? $room->condition : '' }}</td>
                <td class="text-center">{{ in_array($room->condition, [7, 8]) ? $room->condition : '' }}</td>
                <td>{{ $room->corrective_action }}</td>
                <td>{{ $room->verification }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="8" style="text-align: right; border: none;">QM 50 / 00</td>
            </tr>
        </tbody>
    </table>

    <p><strong>Keterangan Pengecekan :</strong></p>
    <ul style="margin-top: 0;">
        <li>Pengecekan Kondisi Bahan baku, bahan penunjang, dan kemasan: nomor 1–6</li>
        <li>Pengecekan Kemasan: nomor 1–2</li>
        <li>Pengecekan Kondisi Mesin dan Peralatan: nomor 3–8</li>
        <li>Pengecekan Kondisi Ruangan: nomor 3–8</li>
    </ul>

    <p><strong>Kriteria Penilaian :</strong></p>
    <ol style="margin-top: 0;">
        <li>Sesuai Spesifikasi</li>
        <li>Tidak Sesuai Spesifikasi</li>
        <li>Bebas dari kontaminan dan bahan sebelumnya;</li>
        <li>Ada kontaminan atau sisa bahan sebelumnya</li>
        <li>Bebas dari potensi kontaminasi allergen;</li>
        <li>Ada potensi kontaminasi allergen;</li>
        <li>Bersih, tidak ada kontaminan atau kotoran, tidak tercium bau menyimpang</li>
        <li>Tidak bersih, ada kontaminan atau kotoran, tercium bau menyimpang</li>
    </ol>

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