<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Proses Pasteurisasi Produk di Retort Chamber</title>
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

    .no-border {
        border: none !important;
    }

    .mb-2 {
        margin-bottom: 1rem;
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
    }

    /* ---- format baru ---- */
    .info {
        margin-bottom: 6px;
    }

    .info td {
        border: none;
        padding: 1px 3px;
    }

    .section-title {
        font-weight: bold;
        font-size: 11px;
        margin: 8px 0 3px;
    }

    .grid th,
    .grid td {
        text-align: center;
        font-size: 9px;
    }

    .grid td.left {
        text-align: left;
    }

    .na {
        background: #d9d9d9;
        font-style: italic;
        font-weight: bold;
        font-size: 8px;
    }

    .red {
        color: #c00;
        font-weight: bold;
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
    </div>

    @php $MPS = \App\Models\MasterPasteurStep::class; @endphp

    @foreach($report->details as $detail)
    <div @if(!$loop->last) style="page-break-after: always;" @endif>

        <h3 class="text-center" style="text-transform: uppercase; margin: 0 0 8px;">
            Verifikasi Proses Pasteurisasi Produk di Retort Chamber
        </h3>

        <div class="section-title">A. Informasi Produk</div>
        <table class="info" style="width: 100%;">
            <tr>
                <td style="width: 130px;">Hari, Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td>Shift</td>
                <td>: {{ $report->shift }}</td>
            </tr>
            <tr>
                <td>Nama Produk</td>
                <td>: {{ $detail->product->product_name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kode Produk</td>
                <td>: {{ $detail->product_code ?? '-' }}</td>
            </tr>
            <tr>
                <td>Gramasi</td>
                <td>: {{ $MPS::fmt(!empty($detail->for_packaging_gr) ? $detail->for_packaging_gr : ($detail->product->nett_weight ?? null)) ?? '-' }} gr</td>
            </tr>
        </table>

        @if($detail->runs->isNotEmpty())
            @foreach($detail->runs as $run)
                @include('report_pasteurs._pdf_run', ['run' => $run])
            @endforeach
        @else
            @include('report_pasteurs._pdf_legacy', ['detail' => $detail])
        @endif
    </div>
    @endforeach

    {{-- C + tanda tangan: sekali, di halaman terakhir --}}
    <div style="page-break-inside: avoid;">
        <div class="section-title" style="margin-top: 14px;">C. Catatan &amp; Dokumentasi</div>
        <div>{!! $report->problem ? nl2br(e($report->problem)) : '-' !!}</div>
        <div style="text-align: right;">{{ $formNumber ?? '-' }}</div>

        <table style="width: 100%; border: none; margin-top: 1.5rem;">
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
    </div>
</body>

</html>