<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pemeriksaan Kedatangan Bahan Baku dan Bahan Penunjang</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 8px;
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 4px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
        background-color: #f0f0f0;
    }

    .text-center { text-align: center; }
    .text-left   { text-align: left; }
    .no-border   { border: none !important; }

    .section-title {
        font-weight: bold;
        margin-top: 10px;
        margin-bottom: 4px;
        font-size: 10px;
    }

    table.info td {
        border: none;
        padding: 1px 2px;
        vertical-align: top;
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

    .row-ng td {
        color: #c0392b;
    }

    @page {
        margin-top: 80px;
        margin-bottom: 45px;
        margin-left: 35px;
        margin-right: 35px;
    }
    </style>
</head>

<body>

    {{-- ===== HEADER FIXED ===== --}}
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
                                        $type   = pathinfo($path, PATHINFO_EXTENSION);
                                        $data   = file_get_contents($path);
                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                    }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="vertical-align: middle; padding-left: 8px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    PT. CHAROEN POKPHAND INDONESIA<br>
                                    FOOD DIVISION<br>
                                    SALATIGA - INDONESIA
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="text-align: right; vertical-align: middle; font-size: 9px; font-weight: bold;">
                    QM 03 / 02
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== JUDUL ===== --}}
    <h3 style="text-align: center; margin: 2px 0 10px 0; font-size: 12px;">
        PEMERIKSAAN KEDATANGAN BAHAN BAKU DAN BAHAN PENUNJANG
    </h3>

    {{-- ===== A. INFORMASI PRODUK ===== --}}
    <div class="section-title">A. Informasi Produk</div>
    <table class="info" style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="130">Hari, Tanggal</td>
            <td width="10">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>:</td>
            <td>{{ $report->shift }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>

    @php
        $groups = $report->details->groupBy('time');
        $areaName = $report->section->section_name ?? '-';
    @endphp

    @foreach ($groups as $time => $groupDetails)

        <table class="info" style="width: 100%; margin-bottom: 2px;">
            <tr>
                <td width="130">Area</td>
                <td width="10">:</td>
                <td>{{ $areaName }}</td>
            </tr>
            <tr>
                <td>Waktu Verifikasi</td>
                <td>:</td>
                <td>{{ $time ?: '-' }}</td>
            </tr>
        </table>

        <table style="margin-bottom: 14px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Bahan Baku</th>
                    <th>Kondisi Bahan</th>
                    <th>Kode Produksi</th>
                    <th>Produsen</th>
                    <th>Suhu</th>
                    <th>Kondisi<br>Kemasan</th>
                    <th>Kenampakan</th>
                    <th>Aroma</th>
                    <th>Warna</th>
                    <th>Status<br>(OK/NG)</th>
                    <th>Tindakan Koreksi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groupDetails->values() as $i => $detail)
                @php
                    $isNg = strtoupper($detail->status ?? '') === 'NG';
                @endphp
                <tr class="{{ $isNg ? 'row-ng' : '' }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left">
                        @if ($detail->material_type === 'premix')
                            {{ $detail->premix?->name ?? '-' }} (Premix)
                        @else
                            {{ $detail->rawMaterial?->material_name ?? '-' }}
                        @endif
                    </td>
                    <td class="text-left">{{ $detail->rm_condition ?? '-' }}</td>
                    <td class="text-center">{{ $detail->production_code ?? '-' }}</td>
                    <td class="text-left">{{ implode(', ', explode(',', $detail->supplier ?? '')) }}</td>
                    <td class="text-center">{{ $detail->temperature ?? '-' }}</td>
                    <td class="text-center">{{ $detail->packaging_condition ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensory_appearance ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensory_aroma ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensory_color ?? '-' }}</td>
                    <td class="text-center">{{ $detail->status ?? '-' }}</td>
                    <td class="text-left">{{ $detail->corrective_action ?? '-' }}</td>
                    <td class="text-left">{{ $detail->problem ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center">Tidak ada data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    @endforeach

    {{-- ===== C. CATATAN & DOKUMENTASI ===== --}}
    <div class="section-title">C. Catatan &amp; Dokumentasi</div>
    <p style="font-size: 10px; margin: 4px 0 16px 0; color: #c0392b;">
        {{ $report->notes ?? '-' }}
    </p>

    {{-- ===== TTD ===== --}}
    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh,<br><br>
                <img src="{{ $createdQr }}" width="80" style="margin: 8px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh,<br><br>
                @if($report->known_by)
                    <img src="{{ $knownQr }}" width="80" style="margin: 8px 0;"><br>
                    <strong>{{ $report->known_by }}</strong><br>
                @else
                    <div style="height: 100px;"></div>
                    <strong>Tanda Tangan &amp; Nama Terang</strong><br>
                @endif
                Foreman / SPV Produksi
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh,<br><br>
                @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="80" style="margin: 8px 0;"><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height: 100px;"></div>
                    <strong>Tanda Tangan &amp; Nama Terang</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

</body>
</html>