<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Foreign Object Contamination Report</title>
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
    }

    .no-border { border: none !important; }
    .text-center { text-align: center; }

    .header {
        position: fixed;
        top: -60px;
        left: 0;
        width: 100%;
        border: none;
    }

    .header-table { width: 100%; border-collapse: collapse; }

    .section-title {
        font-weight: bold;
        font-size: 11px;
        margin-top: 14px;
        margin-bottom: 4px;
    }

    table.info td {
        border: none;
        padding: 2px 2px;
        vertical-align: top;
    }

    table.info td.lbl { width: 140px; }
    table.info td.sep { width: 10px; }

    .evidence-box {
        width: 170px;
        height: 120px;
        text-align: center;
        vertical-align: middle;
    }

    .sign-box {
        height: 110px;
        width: 85%;
        margin: 0 auto;
        text-align: center;
    }

    @page {
        margin-top: 80px;
        margin-bottom: 45px;
        margin-left: 35px;
        margin-right: 35px;
        size: 210mm 330mm;
    }
    </style>
</head>

<body>

    {{-- ===== HEADER FIXED (berulang di tiap halaman) ===== --}}
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
                <td class="no-border"
                    style="text-align: right; vertical-align: middle; font-size: 9px; font-weight: bold;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    @php
        $actionLabels = \App\Models\DetailForeignObject::ACTION_OPTIONS;
    @endphp

    @foreach ($report->details as $detail)
    @php
        $type = $detail->contaminationType;
        $typeText = $type
            ? (($type->category->name ?? '-') . ' / ' . $type->name)
            : ($detail->contaminant_type ?: '-');

        $gramase = !empty($detail->gramase) ? $detail->gramase : ($detail->product->nett_weight ?? null);
        $gramaseText = is_numeric($gramase)
            ? rtrim(rtrim(number_format($gramase, 2, '.', ''), '0'), '.') . ' gr'
            : '-';

        // Investigation: daftar (maks. 5); data lama jatuh ke kolom legacy
        $investigations = array_values(array_filter((array) $detail->investigation, fn ($v) => filled($v)));
        if (empty($investigations)) {
            $investigations = array_values(array_filter([$detail->analysis_stage, $detail->contaminant_origin]));
        }

        // Evidence -> base64 + ukuran yang muat di kotak 220x150
        // Evidence -> daftar gambar base64 (maks. 170x120 px per foto)
        $evidenceImgs = [];
        foreach ($detail->evidence as $path) {
            $full = storage_path('app/public/' . $path);
            if (! is_file($full)) {
                continue;
            }

            [$origW, $origH] = getimagesize($full) ?: [0, 0];
            if ($origW <= 0 || $origH <= 0) {
                continue;
            }

            $ratio = min(170 / $origW, 120 / $origH, 1);
            $ext   = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            $mime  = in_array($ext, ['jpg', 'jpeg']) ? 'jpeg' : $ext;

            $evidenceImgs[] = [
                'src' => 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($full)),
                'w'   => (int) round($origW * $ratio),
                'h'   => (int) round($origH * $ratio),
            ];
        }
    @endphp

    <div @if (!$loop->last) style="page-break-after: always;" @endif>

        <h3 style="text-align: center; margin: 2px 0 10px 0; font-size: 13px; text-transform: uppercase;">
            Foreign Object Contamination Report
        </h3>

        {{-- ===== A - EVENT INFORMATION ===== --}}
        <div class="section-title">A - Event Information</div>
        <table class="info">
            <tr>
                <td class="lbl">Date</td><td class="sep">:</td>
                <td>{{ \Carbon\Carbon::parse($report->date)->locale('en')->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="lbl">Time Found</td><td class="sep">:</td>
                <td>{{ \Carbon\Carbon::parse($detail->time)->format('h.i a') }}</td>
            </tr>
            <tr>
                <td class="lbl">Product / RM</td><td class="sep">:</td>
                <td>{{ $detail->item_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Production code</td><td class="sep">:</td>
                <td>{{ $detail->production_code ?: '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Gramase</td><td class="sep">:</td>
                <td>{{ $gramaseText }}</td>
            </tr>
            <tr>
                <td class="lbl">Location / Shift</td><td class="sep">:</td>
                <td>{{ $report->section->section_name ?? '-' }} / {{ $report->shift ?: '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Process stage / Line</td><td class="sep">:</td>
                <td>{{ $detail->process_stage ?: '-' }} / {{ $detail->line ?: '-' }}</td>
            </tr>
        </table>

        {{-- ===== B - CONTAMINATION DETAILS ===== --}}
        <div class="section-title">B - Contamination Details</div>
        <table class="info">
            <tr>
                <td class="lbl">Category / Type</td><td class="sep">:</td>
                <td>{{ $typeText }}</td>
            </tr>
            <tr>
                <td class="lbl">Size / Quantity</td><td class="sep">:</td>
                <td>{{ $detail->size_quantity ?: '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Description</td><td class="sep">:</td>
                <td>{{ $detail->description ?: '-' }}</td>
            </tr>
        </table>

        {{-- ===== C - PRODUCT CONTROL & DISPOSITION ===== --}}
        <div class="section-title">C - Product Control &amp; Disposition</div>
        <table class="info">
            <tr>
                <td class="lbl">Product Status</td><td class="sep">:</td>
                <td>{{ $actionLabels[$detail->product_status] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Affected Quantity</td><td class="sep">:</td>
                <td>{{ $detail->affected_quantity ?: '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Disposition</td><td class="sep">:</td>
                <td>{{ $actionLabels[$detail->disposition] ?? '-' }}</td>
            </tr>
        </table>

        {{-- ===== D - INVESTIGATION & CORRECTIVE ACTION ===== --}}
        <div class="section-title">D - Investigation &amp; Corrective Action</div>
        <table class="info">
            <tr>
                <td class="lbl">Investigation</td><td class="sep">:</td>
                <td>
                    @if (count($investigations) > 1)
                        @foreach ($investigations as $item)
                            {{ $loop->iteration }}. {{ $item }}<br>
                        @endforeach
                    @else
                        {{ $investigations[0] ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="lbl">Immediate Correction</td><td class="sep">:</td>
                <td>{{ $detail->immediate_correction ?: '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Corrective Action</td><td class="sep">:</td>
                <td>{{ $actionLabels[$detail->corrective_action] ?? '-' }}</td>
            </tr>
        </table>

        {{-- ===== E - EVIDENCE & VERIFICATION ===== --}}
        <div class="section-title">E - Evidence &amp; Verification</div>
        @if (!empty($evidenceImgs))
            <table style="width: auto; border: none; margin-bottom: 4px;">
                @foreach (array_chunk($evidenceImgs, 3, true) as $rowImgs)
                    <tr>
                        @foreach ($rowImgs as $n => $img)
                            <td class="no-border" style="padding: 0 10px 6px 0; vertical-align: top; text-align: center;">
                                <img src="{{ $img['src'] }}" width="{{ $img['w'] }}" height="{{ $img['h'] }}">
                                <div style="font-size: 8px; margin-top: 3px;">Evidence {{ sprintf('%02d', $n + 1) }}</div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @else
            <div style="color: #888;">No image</div>
        @endif

        <div style="margin-top: 8px;">{{ $detail->notes ?: '-' }}</div>

        @if ($loop->last && $report->notes)
            <div style="margin-top: 8px;"><strong>Notes:</strong> {{ $report->notes }}</div>
        @endif

        {{-- ===== TTD ===== --}}
        <table style="width: 100%; border: none; margin-top: 22px;">
            <tr>
                <td class="no-border text-center" style="width: 33%; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 6px;">Diperiksa oleh,</div>
                    <div class="sign-box">
                        <img src="{{ $createdQr }}" width="70" style="margin-top: 8px; margin-bottom: 12px;"><br>
                        <strong>{{ $report->created_by }}</strong>
                    </div>
                    <div style="margin-top: 4px;">QC Inspector</div>
                </td>
                <td class="no-border text-center" style="width: 33%; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 6px;">Diketahui oleh,</div>
                    <div class="sign-box">
                        @if ($report->known_by)
                            <img src="{{ $knownQr }}" width="70" style="margin-top: 8px; margin-bottom: 12px;"><br>
                            <strong>{{ $report->known_by }}</strong>
                        @else
                            <div style="padding-top: 78px;">Tanda Tangan &amp; Nama Terang</div>
                        @endif
                    </div>
                    <div style="margin-top: 4px;">Foreman / SPV Produksi</div>
                </td>
                <td class="no-border text-center" style="width: 33%; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 6px;">Disetujui oleh,</div>
                    <div class="sign-box">
                        @if ($report->approved_by)
                            <img src="{{ $approvedQr }}" width="70" style="margin-top: 8px; margin-bottom: 12px;"><br>
                            <strong>{{ $report->approved_by }}</strong>
                        @else
                            <div style="padding-top: 78px;">Tanda Tangan &amp; Nama Terang</div>
                        @endif
                    </div>
                    <div style="margin-top: 4px;">Supervisor QC</div>
                </td>
            </tr>
        </table>
    </div>
    @endforeach

</body>

</html>