<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Proses Pemasakan di Steam Kettle</title>
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
                <td class="no-border" style="width: 50%; vertical-align: middle;">
                    <table style="border: none; border-collapse: collapse;">
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
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="text-align: right; vertical-align: middle; font-size: 9px; font-weight: bold;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== JUDUL ===== --}}
    <h3 style="text-align: center; margin: 2px 0 10px 0; font-size: 12px; text-transform: uppercase;">
        Verifikasi Proses Pemasakan di Steam Kettle
    </h3>

    @php
        $mainDetail = $report->details->first();
        use Carbon\Carbon;

        $start = Carbon::parse($report->start_time);
        $end = Carbon::parse($report->end_time);
        $totalDuration = abs($end->diffInMinutes($start));
    @endphp

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
        <tr>
            <td>Nama Produk</td>
            <td>:</td>
            <td>{{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kode Produk</td>
            <td>:</td>
            <td>{{ $report->production_code ?? '-' }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi</div>
    <table class="info" style="width: 100%; margin-bottom: 4px;">
        <tr>
            <td width="130">Mesin</td>
            <td width="10">:</td>
            <td>
                @foreach($report->details as $detail)
                    {{ $detail->no_mesin }}@if(!$loop->last), @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <td>Waktu Awal</td>
            <td>:</td>
            <td>{{ $report->start_time }}</td>
        </tr>
        <tr>
            <td>Waktu Akhir</td>
            <td>:</td>
            <td>{{ $report->end_time }}</td>
        </tr>
        <tr>
            <td>Durasi Proses</td>
            <td>:</td>
            <td>{{ $totalDuration ?: '-' }} menit</td>
        </tr>
        <tr>
            <td>Formula</td>
            <td>:</td>
            <td>{{ $report->formula->formula_name ?? '-' }}</td>
        </tr>
    </table>

    <table style="margin-bottom: 14px;">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Bahan Baku</th>
                <th>Berat</th>
                <th>Status<br>(OK/NG)</th>
                <th>Tindakan Koreksi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allRawMaterials = $report->details->flatMap->rawMaterials;
            @endphp
            @forelse ($allRawMaterials as $i => $rm)
            @php
                $isNg = strtoupper($rm->sensory ?? '') === 'TIDAK OK';
            @endphp
            <tr class="{{ $isNg ? 'row-ng' : '' }}">
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-left">
                    @if ($rm->material_type === 'premix')
                        {{ $rm->premix?->name ?? '-' }}
                    @else
                        {{ $rm->rawMaterial?->material_name ?? '-' }}
                    @endif
                </td>
                <td class="text-center">{{ $rm->amount }}</td>
                <td class="text-center">{{ $rm->sensory ?? '-' }}</td>
                <td class="text-left">{{ $rm->corrective_action ?? '-' }}</td>
                <td class="text-left">{{ $rm->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ===== HASIL PEMASAKAN ===== --}}
    <div class="section-title">Hasil Pemasakan</div>
    <table class="info" style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="180">Std. suhu produk (&deg;C)</td>
            <td width="10">:</td>
            <td>{{ $mainDetail->target_temperature ?? '-' }}</td>
        </tr>
        <tr>
            <td>Suhu aktual produk (&deg;C)</td>
            <td>:</td>
            <td>{{ $mainDetail->actual_temperature ?? '-' }}</td>
        </tr>
        <tr>
            <td>Mixing paddle</td>
            <td>:</td>
            <td>{{ $mainDetail && $mainDetail->mixing_paddle_on ? 'On' : ($mainDetail && $mainDetail->mixing_paddle_off ? 'Off' : '-') }}</td>
        </tr>
        <tr>
            <td>Pressure (bar)</td>
            <td>:</td>
            <td>{{ $mainDetail->pressure ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kenampakan</td>
            <td>:</td>
            <td>{{ $mainDetail->appearance ?? '-' }}</td>
        </tr>
        <tr>
            <td>Warna</td>
            <td>:</td>
            <td>{{ $mainDetail->color ?? '-' }}</td>
        </tr>
        <tr>
            <td>Aroma</td>
            <td>:</td>
            <td>{{ $mainDetail->aroma ?? '-' }}</td>
        </tr>
        <tr>
            <td>Rasa</td>
            <td>:</td>
            <td>{{ $mainDetail->taste ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tekstur</td>
            <td>:</td>
            <td>{{ $mainDetail->texture ?? '-' }}</td>
        </tr>
        <tr>
            <td>Status Produk</td>
            <td>:</td>
            <td>{{ $mainDetail->product_status ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tindakan Perbaikan</td>
            <td>:</td>
            <td>{{ $mainDetail->corrective_action ?? '-' }}</td>
        </tr>
        <tr>
            <td>Notes</td>
            <td>:</td>
            <td>{{ $mainDetail->notes ?? '-' }}</td>
        </tr>
    </table>

    {{-- ===== C. CATATAN & DOKUMENTASI ===== --}}
    <div class="section-title">C. Catatan &amp; Dokumentasi</div>
    <p style="font-size: 10px; margin: 4px 0 16px 0;">
        {{ $report->documentation_notes ?? '-' }}
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