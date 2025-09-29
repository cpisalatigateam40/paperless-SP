<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Maurer Cooking</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
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
        size: 210mm 330mm;
    }

    ul {
        margin: unset;
        padding: .5rem;
    }

    li {
        list-style-type: none;
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

    <h3 class="mb-2 text-center">PEMERIKSAAN PEMASAKAN RUMAH ASAP, SHOWERING, DAN COOLING DOWN MAURER</h3>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="text-align: left; border: none;">
                Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
            <td style="text-align: left; border: none;">
                Shift: <span style="text-decoration: underline;"> {{ $report->shift }} </span>
            </td>
        </tr>
    </table>

    <table class="table table-bordered small">
        {{-- Header produk --}}
        <tbody>
            {{-- Header produk --}}
            <tr>
                <td>Nama Produk</td>
                @foreach ($report->details as $detail)
                <td> {{ $detail->product->product_name ?? '-' }} </td>
                @endforeach
            </tr>
            <tr>
                <td>Kode Produksi</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->production_code ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Untuk Kemasan (gr)</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->packaging_weight ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jumlah Trolly</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->trolley_count ?? '-' }}</td>
                @endforeach
            </tr>


            {{-- A. Rumah Asap --}}
            <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold"
                    style="font-weight: bold;">
                    A. Rumah Asap (Smoke House) (Setting/Aktual)
                </td>
            </tr>

            @php
            $steps = [
            ['no' => 1, 'name' => 'SHOWERING', 'fields' => [['db' => 'time_minutes', 'label' => 'Waktu (menit)']]],
            [
            'no' => 2,
            'name' => 'WARMING',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 3,
            'name' => 'DRYINGI',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 4,
            'name' => 'DRYINGII',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 5,
            'name' => 'DRYINGIII',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 6,
            'name' => 'DRYINGIV',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 7,
            'name' => 'DRYINGV',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 8,
            'name' => 'SMOKING',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)']
            ]
            ],
            [
            'no' => 9,
            'name' => 'COOKINGI',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'product_temperature', 'label' => 'Suhu Produk (°C)'],
            [
            'db' => 'time_minutes',
            'label' => 'Waktu
            (menit)'
            ]
            ]
            ],
            [
            'no' => 10,
            'name' => 'COOKINGII',
            'fields' => [
            [
            'db' => 'room_temperature',
            'label' => 'Suhu Ruang
            (°C)'
            ],
            ['db' => 'product_temperature', 'label' => 'Suhu Produk (°C)'],
            [
            'db' => 'time_minutes',
            'label' => 'Waktu
            (menit)'
            ]
            ]
            ],
            ['no' => 11, 'name' => 'EVAKUASI', 'fields' => [['db' => 'time_minutes', 'label' => 'Waktu (menit)']]],
            ];
            @endphp

            @foreach ($steps as $step)
            <tr>
                <td>{{ $step['no'] }} {{ $step['name'] }}</td>
                @foreach ($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            @foreach ($step['fields'] as $field)
            <tr>
                <td>{{ $field['label'] }}</td>
                @foreach ($report->details as $detail)
                @php
                $stepData = $detail->processSteps->first(function ($s) use ($step) {
                return str_starts_with(trim($s->step_name), trim($step['name']));
                });
                $v1 = $stepData ? ($stepData[$field['db'] . '_1'] ?? '-') : '-';
                $v2 = $stepData ? ($stepData[$field['db'] . '_2'] ?? '-') : '-';
                @endphp
                <td>{{ $v1 }} / {{ $v2 }}</td>
                @endforeach
            </tr>
            @endforeach
            @endforeach

            {{-- Lama Proses --}}
            <tr>
                <td>12. LAMA PROSES</td>
                @foreach ($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Mulai</td>
                @foreach ($report->details as $detail)
                <td>{{ optional($detail->totalProcessTime)->start_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Selesai</td>
                @foreach ($report->details as $detail)
                <td>{{ optional($detail->totalProcessTime)->end_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Total Duration</td>
                @foreach ($report->details as $detail)
                @php
                $duration = optional($detail->totalProcessTime)->total_duration;
                @endphp
                <td>{{ $duration ? $duration . ' menit' : '-' }}</td>
                @endforeach
            </tr>

            {{-- Posisi Thermocouple --}}
            <tr>
                <td>13. POSISI THERMOCOUPLE</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->thermocouplePositions as $pos)
                    {{ $pos->position_info ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr>

            {{-- Sensorik --}}
            <tr>
                <td>14. SENSORI</td>
                @foreach ($report->details as $detail)
                <td></td>
                @endforeach
            </tr>
            @foreach(['ripeness' => 'Kematangan', 'aroma' => 'Rasa Aroma', 'texture' => 'Tekstur', 'color' => 'Warna',
            'taste' => 'Rasa']
            as $field =>
            $label)
            <tr>
                <td>{{ $label }}</td>
                @foreach ($report->details as $detail)
                @php
                $value = optional($detail->sensoryCheck)->$field;
                @endphp
                <td>{{ $value === null ? '-' : ($value ? 'OK' : 'Tidak OK') }}</td>
                @endforeach
            </tr>
            @endforeach

            {{-- Bisa/Tidak Bisa Di Ulir --}}
            <tr>
                <td>15. Bisa/Tidak bisa Di Ulir (khusus sosis ayam okey)</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->can_be_twisted === null ? '-' : ($detail->can_be_twisted ? 'Bisa' : 'Tidak Bisa') }}
                </td>
                @endforeach
            </tr>

            {{-- B. Showering & Cooling Down --}}
            <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold">
                    B. Showering & Cooling Down
                </td>
            </tr>

            {{-- 1 SHOWERING --}}
            <tr style="background-color: seashell;">
                <td>1 SHOWERING</td>
                @foreach ($report->details as $detail)
                <td>{{ optional($detail->showeringCoolingDown)->showering_time ?? '-' }}
                </td>
                @endforeach
            </tr>

            {{-- 2 COOLING DOWN --}}
            <tr style="background-color: seashell;">
                <td>2 COOLING DOWN</td>
                @foreach ($report->details as $detail)
                <td></td> {{-- kosong karena judul --}}
                @endforeach
            </tr>

            <tr>
                <td>Suhu Ruangan /ST (°C)</td>
                @foreach ($report->details as $detail)
                @php
                $scd = optional($detail->showeringCoolingDown);
                $v1 = $scd->room_temp_1 ?? '-';
                $v2 = $scd->room_temp_2 ?? '-';
                @endphp
                <td>{{ $v1 }} / {{ $v2 }}</td>
                @endforeach
            </tr>

            <tr>
                <td>Suhu Produk /CT (°C)</td>
                @foreach ($report->details as $detail)
                @php
                $scd = optional($detail->showeringCoolingDown);
                $v1 = $scd->product_temp_1 ?? '-';
                $v2 = $scd->product_temp_2 ?? '-';
                @endphp
                <td>{{ $v1 }} / {{ $v2 }}</td>
                @endforeach
            </tr>

            <tr>
                <td>Waktu (menit)</td>
                @foreach ($report->details as $detail)
                @php
                $scd = optional($detail->showeringCoolingDown);
                $v1 = $scd->time_minutes_1 ?? '-';
                $v2 = $scd->time_minutes_2 ?? '-';
                @endphp
                <td>{{ $v1 }} / {{ $v2 }}</td>
                @endforeach
            </tr>

            <tr>
                <td>Suhu pusat produk setelah keluar (°C)</td>
                @foreach ($report->details as $detail)
                @php
                $scd = optional($detail->showeringCoolingDown);
                $v1 = $scd->product_temp_after_exit_1 ?? '-';
                $v2 = $scd->product_temp_after_exit_2 ?? '-';
                $v3 = $scd->product_temp_after_exit_3 ?? '-';
                @endphp
                <td>{{ $v1 }} / {{ $v2 }} / {{ $v3 }}</td>
                @endforeach
            </tr>

            <tr>
                <td>Suhu rata-rata pusat produk setelah keluar (°C)</td>
                @foreach ($report->details as $detail)
                @php
                $scd = optional($detail->showeringCoolingDown);
                @endphp
                <td>{{ $scd->avg_product_temp_after_exit ?? '-' }}</td>
                @endforeach
            </tr>

            {{-- Cooking Loss --}}
            <!-- <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold"
                    style="font-weight: bold;">
                    C. Cooking Loss
                </td>
            </tr>
            <tr>
                <td>Kode Batch</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->cookingLosses as $loss)
                    {{ $loss->batch_code ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Berat Mentah</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->cookingLosses as $loss)
                    {{ $loss->raw_weight ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Berat Matang</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->cookingLosses as $loss)
                    {{ $loss->cooked_weight ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Lose (kg)</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->cookingLosses as $loss)
                    {{ $loss->loss_kg ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr>
            <tr>
                <td>%</td>
                @foreach ($report->details as $detail)
                <td>
                    @foreach ($detail->cookingLosses as $loss)
                    {{ $loss->loss_percent ?? '-' }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                @endforeach
            </tr> -->

            <tr>
                <td colspan="5" style="text-align: right; border: none;">QM 06 / 01</td>
            </tr>
        </tbody>
    </table>

    <p>Keterangan : √ : OK - : tidak digunakan x : tidak OK</p>

    <br><br>

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