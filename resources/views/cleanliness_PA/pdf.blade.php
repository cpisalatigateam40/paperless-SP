<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Kesesuaian Area Proses Produksi</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 6px;
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: top;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .no-border { border: none !important; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: bold; }

    .section-title {
        font-weight: bold;
        margin-bottom: 4px;
    }

    .info-table td {
        border: none;
        padding: 1px 0;
    }

    .notes-text {
        font-size: 9px;
        font-style: italic;
        margin-top: -2px;
        margin-bottom: 10px;
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

    ul, li {
        margin: 0;
        padding: 2px;
        page-break-inside: avoid;
        list-style-type: none;
    }

    tr, td, th { page-break-inside: avoid; }
    thead { display: table-header-group; }

    @page {
        margin-top: 80px;
        size: A4;
    }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 60%; vertical-align: middle;">
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
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="padding-left: 10px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    PT. CHAROEN POKPHAND INDONESIA<br>
                                    FOOD DIVISION<br>
                                    SALATIGA - INDONESIA
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    QM P.x / 0x
                </td>
            </tr>
        </table>
    </div>

    <h3 class="text-center">VERIFIKASI KESESUAIAN AREA PROSES PRODUKSI</h3>

    {{-- ===== A. INFORMASI PRODUK ===== --}}
    <div class="section-title">A. Informasi Produk</div>
    <table class="info-table" style="width: 60%; margin-bottom: 12px;">
        <tr>
            <td width="120">Hari, Tanggal</td>
            <td width="15">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>:</td>
            <td>{{ $report->shift }}</td>
        </tr>
        <tr>
            <td>Area</td>
            <td>:</td>
            <td>{{ $report->section_name }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>

    @php
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

        $itemLabelMap = [
            // tambahkan mapping label item lain di sini kalau perlu diringkas
        ];
    @endphp

    @foreach($report->details as $dIndex => $detail)

    <table style="width: 100%; border: none; margin-bottom: 2px;">
        <tr style="border: none;">
            <td class="no-border fw-bold" style="width: 30%;">
                {{ $roman[$dIndex] ?? ($dIndex + 1) }}. Waktu Pemeriksaan
            </td>
            <td class="no-border">: {{ $detail->inspection_hour }} WIB</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Item</th>
                <th rowspan="2">Kondisi</th>
                <th rowspan="2">Catatan</th>
                <th rowspan="2">Tindakan<br>Koreksi</th>
                <th rowspan="2">Hasil<br>Verifikasi</th>
                <th colspan="3">
                    Koreksi Lanjutan
                    <div style="font-size: 8px; font-weight: normal; font-style: italic;">
                        (Jika hasil verifikasi sebelumnya tidak ok, maka akan muncul ini. Jika ok, tidak)
                    </div>
                </th>
            </tr>
            <tr>
                <th>Catatan</th>
                <th>Tindakan<br>Koreksi</th>
                <th>Hasil<br>Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail->items as $i => $item)
            @php
                $label = $itemLabelMap[$item->item] ?? $item->item;
                $isSuhuRuang = \Illuminate\Support\Str::startsWith($item->item, 'Suhu ruang');
                $firstFollowup = $item->followups->first();
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $label }}</td>
                <td class="text-center">
                    @if($isSuhuRuang)
                        {{ $item->temperature_actual ?? $item->condition ?? '-' }} / {{ $item->temperature_display ?? $item->condition ?? '-' }}
                    @else
                        {{ $item->condition }}
                    @endif
                </td>
                <td>{{ $item->notes ?? '-' }}</td>
                <td>{{ $item->corrective_action ?? '-' }}</td>
                <td class="text-center">{{ $item->verification ? 'OK' : 'Tidak OK' }}</td>
                <td>{{ $firstFollowup->notes ?? '-' }}</td>
                <td>{{ $firstFollowup->action ?? '-' }}</td>
                <td class="text-center">
                    {{ $firstFollowup ? ($firstFollowup->verification ? 'OK' : 'Tidak OK') : '-' }}
                </td>
            </tr>

            @foreach($item->followups->skip(1) as $fIndex => $followup)
            <tr>
                <td class="text-center"></td>
                <td colspan="5" style="font-style: italic;">
                    Lanjutan #{{ $fIndex + 2 }} untuk item "{{ $label }}"
                </td>
                <td>{{ $followup->notes ?? '-' }}</td>
                <td>{{ $followup->action ?? '-' }}</td>
                <td class="text-center">{{ $followup->verification ? 'OK' : 'Tidak OK' }}</td>
            </tr>
            @endforeach

            @endforeach
        </tbody>
    </table>

    @endforeach

    <br>

    {{-- ===== TANDA TANGAN ===== --}}
    <table style="width: 100%; border: none; margin-top: 2rem;">
        <tr>
            <td class="no-border text-center" style="width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="80"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td class="no-border text-center" style="width: 33%;">
                Diketahui oleh:<br><br>
                @if($report->known_by)
                <img src="{{ $knownQr }}" width="80"><br>
                <strong>{{ $report->known_by }}</strong><br>
                @else
                <div style="height: 80px;"></div>
                <strong>-</strong><br>
                @endif
                SPV/Foreman/Lady Produksi
            </td>
            <td class="no-border text-center" style="width: 33%;">
                Disetujui oleh:<br><br>
                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="80"><br>
                <strong>{{ $report->approved_by }}</strong><br>
                @else
                <div style="height: 80px;"></div>
                <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>
</body>

</html>