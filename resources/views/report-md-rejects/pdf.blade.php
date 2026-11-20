@php
    $cb = fn ($checked) => $checked ? '☑' : '☐';

    $causeLabels = \App\Models\ReportMdRejectDetail::CAUSES;
    $dispLabels  = \App\Models\ReportMdRejectDetail::DISPOSITIONS;
    $resultLabel = ['detected' => 'Terdeteksi', 'not_detected' => 'Tidak Terdeteksi'];

    $details = $report->details->count() ? $report->details->values() : collect([null]);  // minimal 1 baris kosong

    // Mode ringkas supaya tetap 1 halaman
    $n       = $details->count();
    $compact = $n > 5;
    $fs      = $compact ? ($n > 10 ? 5.5 : 6) : 7;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pemeriksaan Produk Reject Metal Detector</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
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
        vertical-align: top;
    }

    th {
        text-align: center;
        vertical-align: middle;
        font-weight: normal;
        background-color: #f0f0f0;
    }

    .text-center { text-align: center; vertical-align: middle; }
    .no-border   { border: none !important; }

    .section-title {
        font-weight: bold;
        margin-top: 8px;
        margin-bottom: 3px;
        font-size: 9px;
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

    table.data {
        table-layout: fixed;
        font-size: {{ $fs }}px;
    }
    table.data th, table.data td {
        word-wrap: break-word;
        padding: {{ $compact ? '1px 2px' : '2px 3px' }};
    }
    table.data tr { page-break-inside: avoid; }

    .chk  { font-size: {{ $fs + 2 }}px; }
    .line { line-height: {{ $compact ? '1.2' : '1.45' }}; }

    @page {
        margin-top: 80px;
        margin-bottom: 40px;
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
                <td class="no-border" style="width: 60%; vertical-align: middle;">
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
                <td class="no-border" style="text-align: right; vertical-align: middle; font-size: 9px; font-weight: bold;">
                    {{ $formNumber ?: 'QF 40/00' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== JUDUL ===== --}}
    <h3 style="text-align: center; margin: 2px 0 8px 0; font-size: 12px; text-transform: uppercase;">
        Pemeriksaan Produk Reject Metal Detector
    </h3>

    <table class="info" style="width: 100%; margin-bottom: 4px;">
        <tr>
            <td width="70">Hari, Tanggal</td>
            <td width="8">:</td>
            <td width="200">{{ $report->date->translatedFormat('l, d F Y') }}</td>
            <td width="40">Shift</td>
            <td width="8">:</td>
            <td width="80">{{ $report->shift }}</td>
            <td width="30">Line</td>
            <td width="8">:</td>
            <td>{{ $report->line }}</td>
        </tr>
    </table>

    {{-- ===== A. VERIFIKASI ALAT ===== --}}
    <div class="section-title">A. Verifikasi Alat (Test Piece)</div>
    <table style="width: 60%;">
        <thead>
            <tr>
                <th style="width: 22%;">Test Piece</th>
                <th style="width: 18%;">Terdeteksi</th>
                <th style="width: 22%;">Tidak Terdeteksi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\ReportMdReject::TEST_PIECES as $piece)
                @php $v = $report->verifications->firstWhere('test_piece', $piece); @endphp
                <tr>
                    <td>{{ $piece }}</td>
                    <td class="text-center chk">{{ $cb(optional($v)->result === 'detected') }}</td>
                    <td class="text-center chk">{{ $cb(optional($v)->result === 'not_detected') }}</td>
                    <td>{{ optional($v)->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== B. DATA PRODUK ===== --}}
    <div class="section-title">B. Data Produk</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 3.5%;">No</th>
                <th style="width: 5.5%;">Jam</th>
                <th style="width: 12%;">Nama Produk</th>
                <th style="width: 9%;">Kode Produksi</th>
                <th style="width: 7%;">Jml Reject<br>(pcs/pack)</th>
                <th style="width: 10%;">Hasil Pengecekan Ulang</th>
                <th style="width: 8%;">Kontaminasi Ditemukan</th>
                <th style="width: 18%;">Dugaan Penyebab</th>
                <th style="width: 14%;">Tindakan Perbaikan / Korektif</th>
                <th style="width: 13%;">Disposisi Produk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $d)
                @php $picked = optional($d)->suspected_causes ?? []; @endphp
                <tr>
                    <td class="text-center">{{ $d ? $loop->iteration : '' }}</td>
                    <td class="text-center">{{ $d ? substr($d->time, 0, 5) : '' }}</td>
                    <td>{{ optional(optional($d)->product)->product_name }}</td>
                    <td class="text-center">{{ optional($d)->production_code }}</td>
                    <td class="text-center">{{ optional($d)->reject_qty }}</td>

                    {{-- Hasil pengecekan ulang --}}
                    <td class="line">
                        @if($compact)
                            {{ $d ? ($resultLabel[$d->recheck_result] ?? '-') : '' }}
                        @else
                            <span class="chk">{{ $cb(optional($d)->recheck_result === 'detected') }}</span> Terdeteksi<br>
                            <span class="chk">{{ $cb(optional($d)->recheck_result === 'not_detected') }}</span> Tidak Terdeteksi
                        @endif
                    </td>

                    {{-- Kontaminasi --}}
                    <td class="line">
                        @if($compact)
                            {{ $d ? (is_null($d->contamination_found) ? '-' : ($d->contamination_found ? 'Ada' : 'Tidak')) : '' }}
                        @else
                            <span class="chk">{{ $cb($d && $d->contamination_found === true) }}</span> Ada<br>
                            <span class="chk">{{ $cb($d && $d->contamination_found === false) }}</span> Tidak
                        @endif
                    </td>

                    {{-- Dugaan penyebab --}}
                    <td class="line">
                        @if($compact)
                            @forelse($picked as $key)
                                <div>• {{ $key === 'other' ? 'Lainnya: ' . (optional($d)->suspected_cause_other ?: '-') : ($causeLabels[$key] ?? $key) }}</div>
                            @empty
                                {{ $d ? '-' : '' }}
                            @endforelse
                        @else
                            @foreach($causeLabels as $key => $label)
                                <div>
                                    • <span class="chk">{{ $cb(in_array($key, $picked, true)) }}</span>
                                    @if($key === 'other')
                                        Lainnya: {{ ($d && in_array('other', $picked, true) && $d->suspected_cause_other) ? $d->suspected_cause_other : '..........' }}
                                    @else
                                        {{ $label }}
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </td>

                    <td>{{ optional($d)->corrective_action }}</td>

                    {{-- Disposisi --}}
                    <td class="line">
                        @if($compact)
                            {{ $d ? ($dispLabels[$d->disposition] ?? '-') : '' }}
                        @else
                            @foreach($dispLabels as $key => $label)
                                <div>• <span class="chk">{{ $cb(optional($d)->disposition === $key) }}</span> {{ $label }}</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== TTD ===== --}}
    <table style="width: 100%; border: none; margin-top: 18px; page-break-inside: avoid;">
        <tr>
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh,<br><br>
                <img src="{{ $createdQr }}" width="60" style="margin: 4px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh,<br><br>
                @if($report->known_by)
                    <img src="{{ $knownQr }}" width="60" style="margin: 4px 0;"><br>
                    <strong>{{ $report->known_by }}</strong><br>
                @else
                    <div style="height: 72px;"></div>
                    <strong>Tanda Tangan &amp; Nama Terang</strong><br>
                @endif
                Foreman / SPV Produksi
            </td>
            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh,<br><br>
                @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="60" style="margin: 4px 0;"><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height: 72px;"></div>
                    <strong>Tanda Tangan &amp; Nama Terang</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

</body>
</html>