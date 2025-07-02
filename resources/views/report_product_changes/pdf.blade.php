<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Verifikasi Pergantian Produk</title>
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

    <h3 class="mb-2 text-center">Laporan Verifikasi Pergantian Produk</h3>

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
                <th rowspan="2">No</th>
                <th rowspan="2" class="text-left">PARAMETER PENGECEKAN</th>
                <th colspan="4">PENILAIAN KONDISI BAHAN / PERALATAN</th>
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
            {{-- === KATEGORI 1: SISA BAHAN DAN KEMASAN === --}}
            <tr>
                <td colspan="8" class="text-left"><strong>SISA BAHAN DAN KEMASAN</strong></td>
            </tr>
            @foreach ($report->materialLeftovers as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ strtoupper($item->item) }}</td>
                <td class="text-center">{{ in_array($item->condition, [1,2]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [3,4]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [5,6]) ? $item->condition : '' }}</td>
                <td class="text-center">{{ in_array($item->condition, [7,8]) ? $item->condition : '' }}</td>
                <td>{{ $item->corrective_action }}</td>
                <td>
                    <ul class="mb-0">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $item->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $item->corrective_action ?? '-' }}
                        </li>

                        @foreach($item->followups as $index => $followup)
                        <li class="mt-2">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $followup->corrective_action ?? '-' }}
                            {{-- kalau ada notes: --}}
                            @if(!empty($followup->notes))
                            <br>Keterangan: {{ $followup->notes }}
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach

            {{-- === KATEGORI 2: MESIN DAN PERALATAN === --}}
            <tr>
                <td colspan="8" class="text-left"><strong>MESIN DAN PERALATAN</strong></td>
            </tr>
            @foreach ($report->equipments as $i => $eq)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ strtoupper($eq->equipment->name ?? '-') }}</td>
                <td class="text-center">{{ in_array($eq->condition, [1,2]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [3,4]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [5,6]) ? $eq->condition : '' }}</td>
                <td class="text-center">{{ in_array($eq->condition, [7,8]) ? $eq->condition : '' }}</td>
                <td>{{ $eq->corrective_action }}</td>
                <td>
                    <ul class="mb-0">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $eq->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $eq->corrective_action ?? '-' }}
                        </li>

                        @foreach($eq->followups as $index => $followup)
                        <li class="mt-2">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $followup->corrective_action ?? '-' }}
                            {{-- kalau ada notes: --}}
                            @if(!empty($followup->notes))
                            <br>Keterangan: {{ $followup->notes }}
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>

            </tr>
            @endforeach

            {{-- === KATEGORI 3: KONDISI RUANGAN === --}}
            <tr>
                <td colspan="8" class="text-left"><strong>KONDISI RUANGAN</strong></td>
            </tr>
            @foreach ($report->sections as $i => $sec)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ strtoupper($sec->section->section_name ?? '-') }}</td>
                <td></td> {{-- kolom 1/2 tidak digunakan untuk ruangan --}}
                <td class="text-center">{{ in_array($sec->condition, [3,4]) ? $sec->condition : '' }}</td>
                <td class="text-center">{{ in_array($sec->condition, [5,6]) ? $sec->condition : '' }}</td>
                <td class="text-center">{{ in_array($sec->condition, [7,8]) ? $sec->condition : '' }}</td>
                <td>{{ $sec->corrective_action }}</td>
                <td>
                    <ul class="mb-0">
                        <li>
                            <strong>Verifikasi Utama:</strong><br>
                            Kondisi: {{ $sec->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $sec->corrective_action ?? '-' }}
                        </li>

                        @foreach($sec->followups as $index => $followup)
                        <li class="mt-2">
                            <strong>Koreksi Lanjutan #{{ $index + 1 }}:</strong><br>
                            Kondisi: {{ $followup->verification == '1' ? 'OK' : 'Tidak OK' }}<br>
                            Tindakan Koreksi: {{ $followup->corrective_action ?? '-' }}
                            {{-- kalau ada notes: --}}
                            @if(!empty($followup->notes))
                            <br>Keterangan: {{ $followup->notes }}
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>

            </tr>
            @endforeach

            <tr>
                <td colspan="8" style="text-align: right; border: none;">QM 51 / 00</td>
            </tr>
        </tbody>
    </table>


    <p><strong>Keterangan Pengecekan :</strong></p>
    <ul style="margin-top: 0;">
        <li>Pengecekan Kondisi Sisa Bahan/Kemasan: nomor 1–8</li>
        <li>Pengecekan Kondisi Mesin dan Peralatan: nomor 3–8</li>
        <li>Pengecekan Kondisi Ruangan: nomor 3–8</li>
    </ul>

    <p><strong>Kriteria Penilaian :</strong></p>
    <ol style="margin-top: 0;">
        <li>Bersih, tidak ada sisa bahan/kemasan sebelumnya;</li>
        <li>Ada sisa bahan/kemasan sebelumnya</li>
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