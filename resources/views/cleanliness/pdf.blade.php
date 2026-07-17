<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Kondisi Ruang Penyimpanan Bahan</title>
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
        text-align: left;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .text-center { text-align: center; }
    .no-border { border: none !important; }
    .mb-2 { margin-bottom: 1rem; }
    .mb-3 { margin-bottom: 1.5rem; }
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

    @page {
        margin-top: 80px;
        size: 210mm 330mm;
    }

    ul { margin: unset; padding: .5rem; }
    li { list-style-type: none; }
    tr, td, th { page-break-inside: avoid; }
    thead { display: table-header-group; }
    </style>
</head>

<body>

    {{-- header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 60%; vertical-align: middle;">
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
                                    PT. CHAROEN POKPHAND INDONESIA<br>
                                    FOOD DIVISION<br>
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

    <h3 class="mb-2 text-center">VERIFIKASI KONDISI RUANG PENYIMPANAN BAHAN BAKU DAN BAHAN PENUNJANG</h3>

    {{-- ===== A. INFORMASI PRODUK ===== --}}
    <div class="section-title">A. Informasi Produk</div>
    <table class="info-table mb-3" style="width: 60%;">
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
            <td>{{ $report->room_name }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>

    @php
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
        $isChillroom = strtolower($report->room_name) === 'chillroom';
        $itemLabelMap = [
            'Suhu ruang (℃) / RH (%)' => 'Suhu Ruang (°C)',
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
                        (Jika hasil verifikasi sebelumnya tidak ok, maka akan muncul ini)
                    </div>
                </th>
            </tr>
            <tr>
                <th>Catatan</th>
                <th>Tindakan<br>Koreksi</th>
                <th>Hasil<br>Verifika</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($detail->items as $item)
                @php
                    $notes = json_decode($item->notes, true);
                    $notesText = is_array($notes) ? implode(', ', $notes) : ($item->notes ?? '-');
                    $firstFollowup = $item->followups->first();
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $itemLabelMap[$item->item] ?? $item->item }}</td>
                    <td class="text-center">{{ $item->condition }}</td>
                    <td>{{ $notesText }}</td>
                    <td>{{ $item->corrective_action ?? '-' }}</td>
                    <td class="text-center">{{ $item->verification ? 'OK' : 'Tidak OK' }}</td>
                    <td>{{ $firstFollowup->notes ?? '-' }}</td>
                    <td>{{ $firstFollowup->corrective_action ?? '-' }}</td>
                    <td class="text-center">
                        {{ $firstFollowup ? ($firstFollowup->verification ? 'OK' : 'Tidak OK') : '-' }}
                    </td>
                </tr>

                @foreach($item->followups->skip(1) as $fIndex => $followup)
                <tr>
                    <td class="text-center"></td>
                    <td colspan="5" style="font-style: italic;">
                        Lanjutan #{{ $fIndex + 2 }} untuk item "{{ $item->item }}"
                    </td>
                    <td>{{ $followup->notes ?? '-' }}</td>
                    <td>{{ $followup->corrective_action ?? '-' }}</td>
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