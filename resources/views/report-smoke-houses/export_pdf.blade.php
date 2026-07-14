<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Proses Pemasakan di Smoke House</title>
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
        margin-bottom: 10px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
        background-color: #f2f2f2;
    }

    .text-center {
        text-align: center;
    }

    .text-start {
        text-align: left !important;
    }

    .no-border {
        border: none !important;
    }

    .fw-bold {
        font-weight: bold;
    }

    .mb-1 {
        margin-bottom: 4px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-3 {
        margin-bottom: 12px;
    }

    .info-table td {
        border: none;
        padding: 1px 0;
    }

    .product-page {
        page-break-after: always;
    }

    .product-page:last-child {
        page-break-after: auto;
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
    </style>
</head>

<body>

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
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="vertical-align: middle; padding-left: 10px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    CHAROEN POKPHAND INDONESIA PT.<br>
                                    FOOD DIVISION<br>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="no-border text-end"
                    style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    QM P.??/??
                </td>
            </tr>
        </table>
    </div>

    @php
    $SHOWERING_PROCESS = 'Showering & Cooling Down';
    @endphp

    @foreach($report->details as $detail)

    @php
    $cookingSteps = $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS);
    $showeringSteps = $detail->steps->where('process_name', '==', $SHOWERING_PROCESS);
    @endphp

    <div class="product-page">

        <h3 class="text-center mb-2">VERIFIKASI PROSES PEMASAKAN DI SMOKE HOUSE</h3>

        {{-- ===== A. INFORMASI PRODUK ===== --}}
        <div class="fw-bold mb-1">A. Informasi Produk</div>
        <table class="info-table mb-3" style="width: 70%;">
            <tr>
                <td width="160">Hari, Tanggal</td>
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
                <td>{{ $detail->product->product_name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kode Produk</td>
                <td>:</td>
                <td>{{ $detail->production_code }}</td>
            </tr>
            <tr>
                <td>Gramasi</td>
                <td>:</td>
                <td>{{ $detail->gramase }} gr</td>
            </tr>
            <tr>
                <td>Smoke House</td>
                <td>:</td>
                <td>{{ $detail->machine_name }}</td>
            </tr>
        </table>

        {{-- ===== B. HASIL VERIFIKASI COOKING ===== --}}
        <div class="fw-bold mb-1">B. Hasil Verifikasi Cooking</div>
        <table class="info-table mb-2" style="width: 70%;">
            <tr>
                <td width="160">Nomor Smoke House</td>
                <td width="15">:</td>
                <td>{{ $detail->smoke_house_no }}</td>
            </tr>
            <tr>
                <td>Jumlah Trolley</td>
                <td>:</td>
                <td>{{ $detail->trolley_count }} trolly</td>
            </tr>
            <tr>
                <td>Jumlah Stick/Trolley</td>
                <td>:</td>
                <td>{{ $detail->stick_count }} stick</td>
            </tr>
            <tr>
                <td>Waktu Proses</td>
                <td>:</td>
                <td>
                    {{ optional($detail->start_process)->format('H:i') }} -
                    {{ optional($detail->end_process)->format('H:i') }}
                </td>
            </tr>
        </table>

        <table class="text-center">
            <tr>
                <th>Parameter</th>
                <th>Setting<br>suhu (&#176;C)</th>
                <th>Aktual<br>suhu (&#176;C)</th>
                <th>Setup<br>time</th>
                <th>Actual<br>time</th>
                <th>Setting<br>RH (%)</th>
                <th>Aktual<br>RH (%)</th>
                <th>Setting<br>Core</th>
                <th>Aktual<br>Core</th>
            </tr>
            @forelse($cookingSteps as $step)
            <tr>
                <td>{{ $step->process_name }}</td>
                <td>{{ $step->setting_temp }}</td>
                <td>{{ $step->actual_temp }}</td>
                <td>{{ $step->setting_time }}</td>
                <td>{{ $step->actual_time }}</td>
                <td>{{ $step->setting_rh }}</td>
                <td>{{ $step->actual_rh }}</td>
                <td>{{ $step->setting_ct }}</td>
                <td>{{ $step->actual_ct }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data</td>
            </tr>
            @endforelse
        </table>

        {{-- ===== COOKING ULANG ===== --}}
        @foreach($detail->reworks as $rework)
        <div class="fw-bold mb-1">Cooking Ulang</div>
        <table class="info-table mb-2" style="width: 70%;">
            <tr>
                <td width="160">Nomor Smoke House</td>
                <td width="15">:</td>
                <td>{{ $rework->smoke_house_no }}</td>
            </tr>
            <tr>
                <td>Jumlah Trolley</td>
                <td>:</td>
                <td>{{ $rework->trolley_count }} trolly</td>
            </tr>
            <tr>
                <td>Jumlah Stick/Trolley</td>
                <td>:</td>
                <td>{{ $rework->stick_count }} stick</td>
            </tr>
            <tr>
                <td>Waktu Proses</td>
                <td>:</td>
                <td>
                    {{ optional($rework->start_process)->format('H:i') }} -
                    {{ optional($rework->end_process)->format('H:i') }}
                </td>
            </tr>
        </table>

        <table class="text-center">
            <tr>
                <th>Parameter</th>
                <th>Setting<br>suhu (&#176;C)</th>
                <th>Aktual<br>suhu (&#176;C)</th>
                <th>Setup<br>time</th>
                <th>Actual<br>time</th>
                <th>Setting<br>RH (%)</th>
                <th>Aktual<br>RH (%)</th>
                <th>Setting<br>Core</th>
                <th>Aktual<br>Core</th>
            </tr>
            @forelse($rework->steps as $step)
            <tr>
                <td>{{ $step->process_name }}</td>
                <td>{{ $step->setting_temp }}</td>
                <td>{{ $step->actual_temp }}</td>
                <td>{{ $step->setting_time }}</td>
                <td>{{ $step->actual_time }}</td>
                <td>{{ $step->setting_rh }}</td>
                <td>{{ $step->actual_rh }}</td>
                <td>{{ $step->setting_ct }}</td>
                <td>{{ $step->actual_ct }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data</td>
            </tr>
            @endforelse
        </table>
        @endforeach

        @if($detail->sensories)
        <div class="mb-1"><strong>Hasil Sensori:</strong></div>
        <table class="info-table mb-3" style="width: 40%;">
            <tr>
                <td width="160">1. Kenampakan</td>
                <td width="15">:</td>
                <td>{{ $detail->sensories->appearance ?: '-' }}</td>
            </tr>
            <tr>
                <td>2. Warna</td>
                <td>:</td>
                <td>{{ $detail->sensories->color ?: '-' }}</td>
            </tr>
            <tr>
                <td>3. Aroma</td>
                <td>:</td>
                <td>{{ $detail->sensories->aroma ?: '-' }}</td>
            </tr>
            <tr>
                <td>4. Rasa</td>
                <td>:</td>
                <td>{{ $detail->sensories->taste ?: '-' }}</td>
            </tr>
            <tr>
                <td>5. Tekstur</td>
                <td>:</td>
                <td>{{ $detail->sensories->texture ?: '-' }}</td>
            </tr>
            <tr>
                <td>Notes</td>
                <td>:</td>
                <td>{{ $detail->sensories->notes ?: '-' }}</td>
            </tr>
        </table>
        @endif

        

        {{-- ===== C. SHOWERING & COOLING DOWN ===== --}}
        <div class="fw-bold mb-1">C. Hasil Verifikasi Showering & Cooling Down</div>
        <table class="text-center">
            <tr>
                <th>Parameter</th>
                <th>Setting<br>suhu (&#176;C)</th>
                <th>Aktual<br>suhu (&#176;C)</th>
                <th>Setup<br>time</th>
                <th>Actual<br>time</th>
                <th>Setting<br>RH (%)</th>
                <th>Aktual<br>RH (%)</th>
                <th>Setting<br>Core</th>
                <th>Aktual<br>Core</th>
            </tr>
            @forelse($showeringSteps as $step)
            <tr>
                <td>{{ $step->process_name }}</td>
                <td>{{ $step->setting_temp }}</td>
                <td>{{ $step->actual_temp }}</td>
                <td>{{ $step->setting_time }}</td>
                <td>{{ $step->actual_time }}</td>
                <td>{{ $step->setting_rh }}</td>
                <td>{{ $step->actual_rh }}</td>
                <td>{{ $step->setting_ct }}</td>
                <td>{{ $step->actual_ct }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data</td>
            </tr>
            @endforelse
        </table>

        <table class="info-table mb-3" style="width: 70%;">
            <tr>
                <td width="160">Proses cooling down selesai</td>
                <td width="15">:</td>
                <td>{{ optional($detail->cooling_finish)->format('H:i') }} WIB</td>
            </tr>
        </table>

        {{-- ===== D. CATATAN & DOKUMENTASI ===== --}}
        <div class="fw-bold mb-1">D. Catatan & Dokumentasi</div>
        <p class="mb-3">{{ $report->notes ?: '-' }}</p>

        {{-- ===== TANDA TANGAN ===== --}}
        <table style="width: 100%; border: none; margin-top: 2rem;">
            <tr style="border: none;">
                <td style="text-align: center; border: none; width: 33%;">
                    Diperiksa oleh:<br><br>
                    <img src="{{ $createdQr }}" width="70" style="margin: 6px 0;"><br>
                    <strong>{{ $report->creator->name ?? '-' }}</strong><br><br>
                    QC Inspector
                </td>
                <td style="text-align: center; border: none; width: 33%;">
                    Diketahui oleh:<br><br>
                    @if($report->known_by)
                    <img src="{{ $knownQr }}" width="70" style="margin: 6px 0;"><br>
                    <strong>{{ $report->known_by }}</strong><br><br>
                    @else
                    <div style="height: 90px;"></div>
                    <strong>-</strong><br>
                    @endif
                    Foreman / SPV Produksi
                </td>
                <td style="text-align: center; border: none; width: 33%;">
                    Disetujui oleh:<br><br>
                    @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="70" style="margin: 6px 0;"><br>
                    <strong>{{ $report->approved_by }}</strong><br><br>
                    @else
                    <div style="height: 90px;"></div>
                    <strong>-</strong><br>
                    @endif
                    Supervisor QC
                </td>
            </tr>
        </table>

    </div>

    @endforeach

</body>

</html>