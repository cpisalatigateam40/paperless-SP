<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Checklist Audit Kepatuhan Proses Packing Primer</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 4px;
    }

    th, td {
        border: 1px solid #000;
        padding: 1px 3px;
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
        margin-top: 6px;
        margin-bottom: 2px;
        font-size: 10px;
    }

    table.info td {
        border: none;
        padding: 0 2px;
        vertical-align: top;
    }

    .header {
        position: fixed;
        top: -55px;
        left: 0;
        width: 100%;
        border: none;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .row-highlight td {
        background-color: #fdf3d0;
        font-weight: bold;
    }

    .signature-block {
        page-break-inside: avoid;
    }

    @page {
        margin-top: 65px;
        margin-bottom: 30px;
        margin-left: 30px;
        margin-right: 30px;
    }
    </style>
</head>

<body>

    {{-- ===== HEADER FIXED ===== --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 50%; vertical-align: middle;">
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
                                    {{ strtoupper($report->area->name ?? '') }} - INDONESIA
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="text-align: right; vertical-align: middle; font-size: 8px; font-weight: bold;">
                    {{ $formNumber ?? '-' }}<br>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== JUDUL ===== --}}
    <h3 style="text-align: center; margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; margin-top: 2rem;">
        Checklist Audit Kepatuhan Proses Packing Primer
    </h3>

    {{-- ===== A. INFORMASI AKTIVITAS ===== --}}
    <div class="section-title">A. Informasi Aktivitas</div>
    <table class="info" style="width: 100%; margin-bottom: 6px;">
        <tr>
            <td width="120">Hari, Tanggal</td>
            <td width="10">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Waktu, Shift</td>
            <td>:</td>
            <td>{{ $report->shift ?? '-' }}</td>
        </tr>
        <tr>
            <td>Area</td>
            <td>:</td>
            <td>{{ $report->section->section_name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Tujuan</td>
            <td style="vertical-align: top;">:</td>
            <td>{{ $report->tujuan ?? '-' }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>
    <table class="info" style="width: 100%; margin-bottom: 6px;">
        <tr>
            <td width="120">Line</td>
            <td width="10">:</td>
            <td>{{ $report->line ?? '-' }}</td>
        </tr>
        <tr>
            <td>Produk</td>
            <td>:</td>
            <td>{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kode Produksi</td>
            <td>:</td>
            <td>{{ $report->production_code ?? '-' }}</td>
        </tr>
        <tr>
            <td>Karyawan</td>
            <td>:</td>
            <td>{{ $report->karyawan ?? '-' }}</td>
        </tr>
    </table>

    {{-- ===== TABEL FOOD SAFETY / FOOD QUALITY / PROCESS COMPLIANCE ===== --}}
    @php
        $categoryLabels = [
            'food_safety' => 'FOOD SAFETY',
            'food_quality' => 'FOOD QUALITY',
            'process_compliance' => 'PROCESS COMPLIANCE',
        ];
        $groupedDetails = $report->details->groupBy(fn ($d) => $d->item->category ?? '');

        $criteria = [
            '10/10' => 'Proses sesuai, produksi dilanjutkan.',
            '9/10' => 'Lakukan perbaikan langsung (briefing, cleaning, penyesuaian mesin), kemudian verifikasi ulang.',
            '<=8/10' => 'Tim melakukan investigasi, produk dihold sesuai kebutuhan. Verifikasi efektivitas proses sebelum produksi dilanjutkan.',
            'food_safety' => 'Hentikan sementara proses, isolasi produk terdampak, investigasi akar penyebab, dan tindakan korektif sesuai prosedur.',
        ];
        $criteriaLabels = [
            '10/10' => '10/10',
            '9/10' => '9/10',
            '<=8/10' => '≤ 8/10',
            'food_safety' => 'Food Safety',
        ];
    @endphp

    @foreach ($categoryLabels as $categoryKey => $label)
        <div class="section-title">{{ $label }}</div>
        <table style="margin-bottom: 6px;">
            <thead>
                <tr>
                    <th style="width: 4%">No</th>
                    <th>Item Verifikasi</th>
                    <th style="width: 6%">Yes</th>
                    <th style="width: 6%">No</th>
                    <th style="width: 22%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groupedDetails->get($categoryKey, collect()) as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $detail->item->item_verifikasi ?? '-' }}</td>
                        <td class="text-center">{{ $detail->verifikasi == 'yes' ? 'V' : '' }}</td>
                        <td class="text-center">{{ $detail->verifikasi == 'no' ? 'V' : '' }}</td>
                        <td class="text-left">{{ $detail->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    {{-- ===== C. HASIL AUDIT & KRITERIA PENILAIAN ===== --}}
    <div class="section-title">C. Hasil Audit &amp; Kriteria Penilaian</div>
    <p style="font-size: 9px; margin: 0 0 4px 0;">
        Berdasarkan hasil checklist yang telah dilakukan, hasil audit kepatuhan packing primer mendapatkan nilai &nbsp;
        <strong>{{ $criteriaLabels[$report->audit_score] ?? ($report->audit_score ?? '-') }}</strong>
    </p>
    <table style="margin-bottom: 2px;">
        <thead>
            <tr>
                <th style="width: 18%">Hasil Audit</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($criteria as $key => $tindakan)
                <tr class="{{ $report->audit_score == $key ? 'row-highlight' : '' }}">
                    <td class="text-center">{{ $criteriaLabels[$key] }}</td>
                    <td>{{ $tindakan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($report->tindakan)
        <p style="font-size: 9px; margin: 4px 0 0 0;">
            <strong>Tindakan yang diambil:</strong> {{ $report->tindakan }}
        </p>
    @endif

    {{-- ===== TTD PELAKSANA ===== --}}
    <table class="signature-block" style="width: 100%; border: none; margin-top: 12px;">
        <tr>
            <td style="text-align: right; border: none; width: 100%;">
                Pelaksana,<br>
                <img src="{{ $createdQr }}" width="60" style="margin: 4px 0;"><br>
                <strong>{{ $report->createdBy->name ?? $report->created_by }}</strong><br>
                Supervisor/Leader QC/Produksi
            </td>
        </tr>
    </table>

</body>
</html>