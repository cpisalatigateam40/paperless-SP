<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export PDF - Laporan Baso Cooking</title>
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

    .signature-box {
        height: 40px;
        border-bottom: 1px solid #000;
        margin-top: 20px;
        width: 60%;
    }

    .no-border {
        border: none !important;
    }

    .mb-2 {
        margin-bottom: 1rem;
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

    .mb-4 {
        margin-bottom: 2rem;
    }

    .underline {
        text-decoration: underline;
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
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" alt="Logo" style="width: 50px;">
                            </td>
                            <td class="no-border" style="vertical-align: middle; padding-left: 10px;">
                                <div style="font-size: 9px; font-weight: bold; line-height: 1.2;">
                                    CHAROEN<br>POKPHAND<br>INDONESIA PT.<br>Food Division
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="text-align:center;">PEMERIKSAAN PEMASAKAN BASO</h3>

    <p><strong>Tanggal:</strong> {{ $report->date }} |
        <strong>Shift:</strong> {{ $report->shift }} |
        <strong>Produk:</strong> {{ $report->product->product_name ?? '-' }} -
        {{ $report->product->nett_weight ?? '-' }} g |
        <strong>STD Suhu Pusat (&deg;C):</strong> {{ $report->std_core_temp ?? '-' }} |
        <strong>STD berat akhir/potong:</strong> {{ $report->std_weight ?? '-' }} |
        <strong>Set suhu tangki perebusan 1 (&deg;C):</strong> {{ $report->set_boiling_1 ?? '-' }} |
        <strong>Set suhu tangki perebusan 2 (&deg;C):</strong> {{ $report->set_boiling_2 ?? '-' }} |
    </p>

    <table class="table table-bordered table-sm text-center align-middle">
        <thead class="table-secondary">
            <tr>
                <th rowspan="2" class="align-middle">Kode Produksi</th>
                <th rowspan="2" class="align-middle">Pukul</th>
                <th rowspan="2">Emulsi (°C)</th>
                <th rowspan="2">Air Tangki I (°C)</th>
                <th rowspan="2">Air Tangki II (°C)</th>
                <th rowspan="2">Berat Awal (gr)</th>

                <th colspan="6">Suhu Baso (°C)</th>

                <th colspan="5" rowspan="2">Uji Sensori</th>
                <th rowspan="2">Berat Akhir (gr)</th>
                <th colspan="2" rowspan="2">Paraf</th>
            </tr>
            <tr>
                {{-- Suhu Baso --}}
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->details as $detail)
            @php
            $awal = $detail->temperatures->where('time_type', 'awal')->first();
            $akhir = $detail->temperatures->where('time_type', 'akhir')->first();
            @endphp

            {{-- Baris 1 (awal) --}}
            <tr>
                <td>{{ $detail->production_code }}</td>
                <td>{{ $awal?->time_recorded ? \Carbon\Carbon::parse($awal->time_recorded)->format('H:i') : '-' }}
                </td>
                <td>{{ $detail->emulsion_temp }}</td>
                <td>{{ $detail->boiling_tank_temp_1 }}</td>
                <td>{{ $detail->boiling_tank_temp_2 }}</td>
                <td>{{ $detail->initial_weight }}</td>

                {{-- Suhu baso awal --}}
                <td>{{ $awal?->baso_temp_1 }}</td>
                <td>{{ $awal?->baso_temp_2 }}</td>
                <td>{{ $awal?->baso_temp_3 }}</td>
                <td>{{ $awal?->baso_temp_4 }}</td>
                <td>{{ $awal?->baso_temp_5 }}</td>
                <td>{{ $awal?->avg_baso_temp }}</td>

                {{-- Sensori --}}
                <td rowspan="2">{{ $detail->sensory_shape ? 'OK' : 'Tdk OK' }}</td>
                <td rowspan="2">{{ $detail->sensory_taste ? 'OK' : 'Tdk OK' }}</td>
                <td rowspan="2">{{ $detail->sensory_aroma ? 'OK' : 'Tdk OK' }}</td>
                <td rowspan="2">{{ $detail->sensory_texture ? 'OK' : 'Tdk OK' }}</td>
                <td rowspan="2">{{ $detail->sensory_color ? 'OK' : 'Tdk OK' }}</td>

                {{-- Berat akhir & paraf --}}
                <td rowspan="2">{{ $detail->final_weight }}</td>
                <td rowspan="2">
                    @php
                    $qcBase64 = null;
                    if ($detail->qc_paraf) {
                    $qcPath = storage_path('app/public/' . $detail->qc_paraf);
                    if (file_exists($qcPath)) {
                    $type = pathinfo($qcPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($qcPath);
                    $qcBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                    }
                    @endphp

                    @if($qcBase64)
                    <img src="{{ $qcBase64 }}" alt="QC" width="60">
                    @endif
                </td>
                <td rowspan="2">
                    @php
                    $prodBase64 = null;
                    if ($detail->prod_paraf) {
                    $prodPath = storage_path('app/public/' . $detail->prod_paraf);
                    if (file_exists($prodPath)) {
                    $type = pathinfo($prodPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($prodPath);
                    $prodBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                    }
                    @endphp

                    @if($prodBase64)
                    <img src="{{ $prodBase64 }}" alt="Produksi" width="60">
                    @endif
                </td>
            </tr>

            {{-- Baris 2 (akhir) --}}
            <tr>
                <td></td>
                <td>{{ $akhir?->time_recorded ? \Carbon\Carbon::parse($akhir->time_recorded)->format('H:i') : '-' }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                {{-- Suhu baso akhir --}}
                <td>{{ $akhir?->baso_temp_1 }}</td>
                <td>{{ $akhir?->baso_temp_2 }}</td>
                <td>{{ $akhir?->baso_temp_3 }}</td>
                <td>{{ $akhir?->baso_temp_4 }}</td>
                <td>{{ $akhir?->baso_temp_5 }}</td>
                <td>{{ $akhir?->avg_baso_temp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br> <br>
    <table style="width: 100%; border: none; margin-top: 4rem;">
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