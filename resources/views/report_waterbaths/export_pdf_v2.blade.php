<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Proses Pasteurisasi Produk di Waterbath</title>
    <style>
    @font-face {
        font-family: "DejaVu Sans";
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format("truetype");
    }

    @page { margin: 80px 35px 35px 35px; }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 9.5px;
    }

    table { border-collapse: collapse; width: 100%; }

    .grid th, .grid td {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: middle;
    }
    .grid th { text-align: center; font-weight: bold; background: #f2f2f2; }
    .grid td.param { text-align: left; width: 14%; }
    .grid td.c { text-align: center; }

    .no-border, .no-border td { border: none !important; }
    .info td { padding: 1px 2px; vertical-align: top; }
    .info .lbl { width: 105px; }
    .info .sep { width: 8px; }

    .section-title { font-size: 11px; font-weight: bold; margin: 10px 0 3px; }
    .run { page-break-inside: avoid; margin-bottom: 10px; }
    .small { font-size: 8px; color: #555; }
    .red { color: #d00000; font-weight: bold; }

    .header { position: fixed; top: -60px; left: 0; width: 100%; }
    </style>
</head>

@php
    $parameters = \App\Models\MasterWaterbathParameter::PARAMETERS;
    $val = fn ($v) => filled($v) ? $v : '-';
    // merah kalau di luar range (true = ok, false = di luar, null = tidak dinilai)
    $red = fn (?bool $ok) => $ok === false ? 'red' : '';
@endphp

<body>
    {{-- header --}}
    <div class="header">
        <table class="no-border">
            <tr>
                <td class="no-border" style="width: 55px; vertical-align: middle;">
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
    </div>

    <h3 style="text-align: center; text-transform: uppercase; margin: 0 0 8px;">
        Verifikasi Proses Pasteurisasi Produk di Waterbath
    </h3>

    {{-- A. Informasi Produk --}}
    <div class="section-title">A. Informasi Produk</div>
    <table class="info no-border">
        <tr>
            <td class="lbl">Hari, Tanggal</td><td class="sep">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Shift</td><td class="sep">:</td>
            <td>{{ $val($report->shift) }}</td>
        </tr>
        <tr>
            <td class="lbl">Nama Produk</td><td class="sep">:</td>
            <td>{{ $val($report->product->product_name ?? null) }}</td>
        </tr>
        <tr>
            <td class="lbl">Kode Produk</td><td class="sep">:</td>
            <td>{{ $val($report->batches->first()?->batch_code) }}</td>
        </tr>
        <tr>
            <td class="lbl">Gramasi</td><td class="sep">:</td>
            <td>{{ filled($report->gramase) ? $report->gramase . ' gr' : '-' }}</td>
        </tr>
    </table>

    {{-- B. Hasil Verifikasi + Pasteurisasi Ulang --}}
    @foreach($report->runs as $run)
        <div class="run">
            <div class="section-title">
                @if($run->run_number === 1)
                    B. Hasil Verifikasi
                @else
                    Pasteurisasi Ulang{{ $run->run_number > 2 ? ' #' . ($run->run_number - 1) : '' }}
                @endif
            </div>

            <table class="info no-border">
                <tr>
                    <td class="lbl">Nomor Waterbath</td><td class="sep">:</td>
                    <td>{{ $val($run->waterbath_number) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Initial Temperature</td><td class="sep">:</td>
                    <td>
                        <span class="{{ $red($run->isInitialTempOk()) }}">
                            {{ filled($run->initial_temp) ? $run->initial_temp . ' °C' : '-' }}
                        </span>
                        <span class="small">(Std: {{ $run->initial_temp_range }} °C)</span>
                    </td>
                </tr>
            </table>

            <table class="grid" style="margin-top: 4px;">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Set. Temp. (°C)</th>
                        <th>Act. Temp. (°C)</th>
                        <th>Set. Time<br>(minutes)</th>
                        <th>Act. Time<br>(minutes)</th>
                        <th>Std. CT Prod.<br>(°C)</th>
                        <th>Act. CT Prod.<br>(°C)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parameters as $key => $label)
                    @php $p = $run->parameter($key); @endphp
                    <tr>
                        <td class="param">{{ $label }}</td>

                        @if($p)
                            {{-- Set Temp --}}
                            <td class="c">
                                @if($p->isDrying())
                                    Hot zone: {{ $p->set_temp_range }}<br>
                                    Cold zone: {{ $p->cold_zone_set_range }}
                                @else
                                    {{ $p->set_temp_range }}
                                @endif
                            </td>

                            {{-- Act Temp --}}
                            <td class="c">
                                @if($p->isDrying())
                                    Hot zone: <span class="{{ $red($p->isActTempOk()) }}">{{ $val($p->act_temp) }}</span><br>
                                    Cold zone: <span class="{{ $red($p->isColdZoneActOk()) }}">{{ $val($p->cold_zone_act) }}</span>
                                @else
                                    <span class="{{ $red($p->isActTempOk()) }}">{{ $val($p->act_temp) }}</span>
                                @endif
                            </td>

                            {{-- Time --}}
                            <td class="c">{{ $val($p->set_time) }}</td>
                            <td class="c"><span class="{{ $red($p->isActTimeOk()) }}">{{ $val($p->act_time) }}</span></td>

                            {{-- CT Prod --}}
                            <td class="c">{{ $p->std_ct_range }}</td>
                            <td class="c"><span class="{{ $red($p->isActCtOk()) }}">{{ $val($p->act_ct) }}</span></td>
                        @else
                            <td class="c" colspan="6">-</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 3px;">
                Sensori:
                @if($run->sensor_ok === null) -
                @else {{ $run->sensor_ok ? 'Ok' : 'Tidak Ok' }}
                @endif
            </div>
            <div>Notes: {{ $val($run->notes) }}</div>
        </div>
    @endforeach

    {{-- C. Catatan & Dokumentasi --}}
    @php
        // gabungkan notes semua run (urut run_number), dipisah koma
        $allNotes = $report->runs
            ->pluck('notes')
            ->map(fn ($n) => rtrim(trim(preg_replace('/\s+/', ' ', (string) $n)), ' .,;'))
            ->filter()
            ->implode(', ');
    @endphp

    <div class="section-title">C. Catatan &amp; Dokumentasi</div>
    <div>{{ $allNotes !== '' ? $allNotes : '-' }}</div>

    {{-- Tanda tangan --}}
    <table class="no-border" style="margin-top: 18px; page-break-inside: avoid;">
        <tr>
            <td class="no-border" style="text-align: center; width: 33%;">
                Diperiksa oleh:<br>
                <img src="{{ $createdQr }}" width="70" style="margin: 6px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td class="no-border" style="text-align: center; width: 33%;">
                Diketahui oleh:<br>
                @if($report->known_by)
                    <img src="{{ $knownQr }}" width="70" style="margin: 6px 0;"><br>
                    <strong>{{ $report->known_by }}</strong><br>
                @else
                    <div style="height: 82px;"></div>
                    <strong>-</strong><br>
                @endif
                Foreman / SPV Produksi
            </td>
            <td class="no-border" style="text-align: center; width: 33%;">
                Disetujui oleh:<br>
                @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="70" style="margin: 6px 0;"><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height: 82px;"></div>
                    <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

    <div style="text-align: right; margin-top: 6px;">{{ $formNumber ?? '-' }}</div>
</body>

</html>