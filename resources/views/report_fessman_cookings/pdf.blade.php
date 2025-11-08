<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Fessman Cooking</title>
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

    .no-border {
        border: none !important;
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
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="no-border" style="width: 30%;">
                    <table style="border: none;">
                        <tr>
                            <td class="no-border" style="width: 50px;">
                                @php
                                $path = public_path('storage/image/logo.png');
                                if (file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                @endphp
                                <img src="{{ $base64 ?? '' }}" style="width: 50px;">
                            </td>
                            <td class="no-border" style="padding-left: 10px;">
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

    <h3 class="text-center">PEMERIKSAAN PEMASAKAN, SHOWERING, DAN COOLING DOWN FESSMAN</h3>

    <table style="border: none;">
        <tr style="border: none;">
            <td style="border: none;">Hari/Tanggal: <span
                    class="underline">{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}</span>
            </td>
            <td style="border: none;">Shift: <span class="underline">{{ $report->shift }}</span></td>
        </tr>
    </table>

    <table class="table table-bordered small">
        <tbody>
            {{-- Produk --}}
            <tr>
                <td>Nama Produk</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->product->product_name ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Gramase</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->product->nett_weight ?? '-' }} g</td>
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
                <td>Jumlah Trolley</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->trolley_count ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Mulai</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->start_time ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Jam Selesai</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->end_time ?? '-' }}</td>
                @endforeach
            </tr>

            {{-- A. Tahap Pemasakan --}}
            <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold"
                    style="font-weight: bold;">
                    A. Tahap Pemasakan (Setting / Aktual)
                </td>
            </tr>

            @php
            $steps = [
            'DRYINGI',
            'DRYINGII',
            'DRYINGIII',
            'DRYINGIV',
            'DRYINGV',
            'DOOR OPENING SECTION 1',
            'PUT CORE PROBE',
            'SMOKING',
            'COOKINGI',
            'COOKINGII',
            'STEAM SUCTION',
            'DOOR OPENING SECTION 1',
            'REMOVE CORE PROBE',
            'FURTHER TRANSPORT'
            ];
            $fields = [
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)'],
            ['db' => 'room_temp', 'label' => 'Suhu Ruang (°C)'],
            ['db' => 'air_circulation', 'label' => 'Sirkulasi Udara'],
            ['db' => 'product_temp', 'label' => 'Suhu Produk (°C)'],
            ['db' => 'actual_product_temp', 'label' => 'Suhu Aktual Produk']
            ];
            @endphp

            @foreach ($steps as $stepName)
            <tr>
                <td>{{ $stepName }}</td>
                @foreach ($report->details as $detail)
                <td></td>
                @endforeach
            </tr>

            @foreach ($fields as $field)
            @php
            $hasAnyValue = false;
            foreach ($report->details as $detail) {
            $stepData = collect($detail->processSteps ?? [])->first(function ($s) use ($stepName) {
            return trim($s->step_name) == trim($stepName);
            });
            if ($field['db'] == 'actual_product_temp') {
            if (!empty($stepData?->actual_product_temp)) {
            $hasAnyValue = true;
            break;
            }
            } else {
            if (!empty($stepData?->{$field['db'] . '_1'}) || !empty($stepData?->{$field['db'] . '_2'})) {
            $hasAnyValue = true;
            break;
            }
            }
            }
            @endphp

            @if ($hasAnyValue)
            <tr>
                <td>{{ $field['label'] }}</td>
                @foreach ($report->details as $detail)
                @php
                $stepData = collect($detail->processSteps ?? [])->first(function ($s) use ($stepName) {
                return trim($s->step_name) == trim($stepName);
                });
                $v1 = $stepData[$field['db'] . '_1'] ?? '-';
                $v2 = $stepData[$field['db'] . '_2'] ?? '-';
                $actual = $stepData['actual_product_temp'] ?? '-';
                @endphp
                @if ($field['db'] == 'actual_product_temp')
                <td>{{ $actual ?: '-' }}</td>
                @else
                <td>{{ ($v1 !== '-' || $v2 !== '-') ? "$v1 / $v2" : '-' }}</td>
                @endif
                @endforeach
            </tr>
            @endif
            @endforeach
            @endforeach

            {{-- B. Sensorik --}}
            <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold">B. Sensorik</td>
            </tr>
            @foreach([
            'ripeness' => 'Kematangan',
            'aroma' => 'Aroma',
            'taste' => 'Rasa',
            'texture' => 'Tekstur',
            'color'
            => 'Warna'
            ]
            as $field => $label)
            <tr>
                <td>{{ $label }}</td>
                @foreach ($report->details as $detail)
                @php $v = optional($detail->sensoryCheck)->$field; @endphp
                <td>{{ $v === null ? '-' : ($v ? 'OK' : 'Tidak OK') }}</td>
                @endforeach
            </tr>
            @endforeach
            <tr>
                <td>Bisa / Tidak Bisa Di Ulir</td>
                @foreach ($report->details as $detail)
                <td>{{ $detail->sensoryCheck->can_be_twisted === null ? '-' : ($detail->sensoryCheck->can_be_twisted ? 'Bisa' : 'Tidak Bisa') }}
                </td>
                @endforeach
            </tr>

            {{-- C. Cooling --}}
            <tr class="table-secondary">
                <td colspan="{{ 1 + $report->details->count() }}" class="text-start fw-semibold"
                    style="font-weight: bold;">C. Tahap
                    Cooling
                    (Setting / Aktual)</td>
            </tr>
            @php
            $coolingSteps = [
            'AIR COOLING WITH SHOWER INTER 1',
            'BLOWER SHOWER OUT SECTION 2',
            'AIR COOLING WITH SHOWER INTER 2',
            'OUT TRANSPORT',
            'SUHU PRODUK KELUAR',
            ];
            $coolingFields = [
            ['db' => 'time_minutes', 'label' => 'Waktu (menit)'],
            ['db' => 'rh', 'label' => 'RH (%)'],
            ['db' => 'product_temp_after_exit', 'label' => 'Suhu Pusat Produk Setelah Keluar (°C)'],
            ['db' => 'avg_product_temp_after_exit', 'label' => 'Suhu Rata-rata Pusat Produk'],
            ];
            @endphp

            @foreach ($coolingSteps as $stepName)
            <tr>
                <td>{{ $stepName }}</td>
                @foreach ($report->details as $detail)
                <td></td>
                @endforeach
            </tr>

            @foreach ($coolingFields as $field)
            @php
            $hasAnyValue = false;
            foreach ($report->details as $detail) {
            $stepData = collect($detail->coolingDowns ?? [])->first(function ($s) use ($stepName) {
            return trim($s->step_name) == trim($stepName);
            });
            if
            (
            in_array($field['db'], [
            'avg_product_temp_after_exit',
            'raw_weight',
            'cooked_weight',
            'loss_kg',
            'loss_percent'
            ])
            ) {
            if (!empty($stepData?->{$field['db']})) {
            $hasAnyValue = true;
            break;
            }
            } elseif ($field['db'] == 'product_temp_after_exit') {
            if (
            !empty($stepData?->product_temp_after_exit_1) || !empty($stepData?->product_temp_after_exit_2) ||
            !empty($stepData?->product_temp_after_exit_3)
            ) {
            $hasAnyValue = true;
            break;
            }
            } else {
            if (!empty($stepData?->{$field['db'] . '_1'}) || !empty($stepData?->{$field['db'] . '_2'})) {
            $hasAnyValue = true;
            break;
            }
            }
            }
            @endphp
            @if($hasAnyValue)
            <tr>
                <td>{{ $field['label'] }}</td>
                @foreach ($report->details as $detail)
                @php
                $stepData = collect($detail->coolingDowns ?? [])->first(function ($s) use ($stepName) {
                return trim($s->step_name) == trim($stepName);
                });
                $v1 = $stepData[$field['db'] . '_1'] ?? '-';
                $v2 = $stepData[$field['db'] . '_2'] ?? '-';
                $v3 = $stepData[$field['db'] . '_3'] ?? '-';
                $single = $stepData[$field['db']] ?? '-';
                @endphp
                @if(
                in_array($field['db'], [
                'avg_product_temp_after_exit',
                'raw_weight',
                'cooked_weight',
                'loss_kg',
                'loss_percent'
                ])
                )
                <td>{{ $single }}</td>
                @elseif($field['db'] == 'product_temp_after_exit')
                <td>{{ "$v1 / $v2 / $v3" }}</td>
                @else
                <td>{{ "$v1 / $v2" }}</td>
                @endif
                @endforeach
            </tr>
            @endif
            @endforeach
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right; border: none;">QM 29 / 01</td>
            </tr>
        </tbody>
    </table>

    <p>Keterangan : √ : OK - : tidak digunakan x : tidak OK</p>

    <br><br>

    {{-- Footer QR --}}
    <table style="width: 100%; border: none;">
        <tr>
            <td style="text-align: center; border: none; width: 33%;">
                Dibuat oleh:<br><br>
                <img src="{{ $createdQr }}" width="80"><br>
                <strong>{{ $report->created_by }}</strong><br>QC Inspector
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
                <img src="{{ $approvedQr }}" width="80"><br>
                <strong>{{ $report->approved_by }}</strong><br>
                @else
                <div style="height: 120px;"></div>
                <strong>-</strong>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>
</body>

</html>