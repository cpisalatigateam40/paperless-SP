<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Proses Pemasakan di Boiling Tank</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        margin-top: 10px;
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
        size: 330mm 230mm;
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
                                    {{ strtoupper($report->area->name ?? '') }} - INDONESIA
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <h3 class="mb-2 text-center" style="text-transform: uppercase;">
        Verifikasi Proses Pemasakan di Boiling Tank
    </h3>

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
            <td>Nama Produk</td>
            <td>:</td>
            <td>{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kode Produk</td>
            <td>:</td>
            <td>{{ $report->product_code ?? '-' }}</td>
        </tr>
        <tr>
            <td>Gramasi</td>
            <td>:</td>
            <td>{{ $report->gramasi ?? '-' }} gr</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI COOKING ===== --}}
    <div class="section-title">B. Hasil Verifikasi Cooking</div>
    <table class="info-table mb-2" style="width: 60%;">
        <tr>
            <td width="120">Line Boiling Tank</td>
            <td width="15">:</td>
            <td>{{ $report->line_boiling_tank ?? '-' }}</td>
        </tr>
        <tr>
            <td>Waktu Proses</td>
            <td>:</td>
            <td>{{ $report->waktu_proses_start ?? '--:--' }} - {{ $report->waktu_proses_end ?? '--:--' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Kode Produksi</th>
                <th rowspan="2">Start</th>
                <th rowspan="2">End</th>
                <th rowspan="2">Suhu Adonan (°C)<br><span style="font-weight: normal;">Std 16 ± 2°C</span></th>
                <th rowspan="2">Aktual Suhu Air<br>Tangki I (°C)<br><span style="font-weight: normal;">Std 75-85°C</span></th>
                <th rowspan="2">Aktual Suhu Air<br>Tangki II (°C)<br><span style="font-weight: normal;">Std 85-95°C</span></th>
                <th colspan="{{ $maxChecks }}">Berat Mentah (gr)<br><span style="font-weight: normal;">Std 11-12 gr</span></th>
                <th colspan="{{ $maxChecks }}">Actual Core<br>Temp. (°C)<br><span style="font-weight: normal;">Std 12°C</span></th>
                <th colspan="{{ $maxChecks }}">Berat Matang (gr)<br><span style="font-weight: normal;">Std 12 gr</span></th>
                <th colspan="{{ $maxChecks }}">Suhu After<br>Cooling (°C)</th>
                <th rowspan="2">Bentuk</th>
                <th rowspan="2">Warna</th>
                <th rowspan="2">Aroma</th>
                <th rowspan="2">Rasa</th>
                <th rowspan="2">Tekstur</th>
            </tr>
            <tr>
                @for($i = 1; $i <= $maxChecks; $i++) <th>{{ $i }}</th> @endfor
                @for($i = 1; $i <= $maxChecks; $i++) <th>{{ $i }}</th> @endfor
                @for($i = 1; $i <= $maxChecks; $i++) <th>{{ $i }}</th> @endfor
                @for($i = 1; $i <= $maxChecks; $i++) <th>{{ $i }}</th> @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($report->details as $detail)
                <tr>
                    <td>{{ $detail->kode_produksi ?? '-' }}</td>
                    <td class="text-center">{{ $detail->start ?? '-' }}</td>
                    <td class="text-center">{{ $detail->end ?? '-' }}</td>
                    <td class="text-center">{{ $detail->suhu_adonan ?? '-' }}</td>
                    <td class="text-center">{{ $detail->aktual_suhu_tangki_1 ?? '-' }}</td>
                    <td class="text-center">{{ $detail->aktual_suhu_tangki_2 ?? '-' }}</td>

                    @for($i = 0; $i < $maxChecks; $i++)
                        <td class="text-center">{{ $detail->checks->get($i)?->berat_mentah ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td class="text-center">{{ $detail->checks->get($i)?->actual_core_temp ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td class="text-center">{{ $detail->checks->get($i)?->berat_matang ?? '-' }}</td>
                    @endfor
                    @for($i = 0; $i < $maxChecks; $i++)
                        <td class="text-center">{{ $detail->checks->get($i)?->suhu_after_cooling ?? '-' }}</td>
                    @endfor

                    <td class="text-center">{{ $detail->sensori_bentuk ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensori_warna ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensori_aroma ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensori_rasa ?? '-' }}</td>
                    <td class="text-center">{{ $detail->sensori_tekstur ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 11 + ($maxChecks * 4) }}" class="text-center">Belum ada Kode Produksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ===== D. CATATAN & DOKUMENTASI ===== --}}
    <div class="section-title">D. Catatan & Dokumentasi</div>
    <p>
        {{ $report->link_kurva ?? '-' }}
    </p>

    <br>

    {{-- ===== TANDA TANGAN ===== --}}
    <table style="width: 100%; border: none; margin-top: 1rem;">
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
                Foreman/SPV Produksi
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