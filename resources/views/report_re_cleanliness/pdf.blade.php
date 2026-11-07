<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</title>
    <style>
    @page { margin: 85px 35px 45px 35px; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 8px;
        line-height: 1.3;
        margin: 0;
        color: #111;
    }

    table { border-collapse: collapse; width: 100%; table-layout: fixed; }

    .header { position: fixed; top: -65px; left: 0; width: 100%; }
    .no-border, .no-border td { border: none !important; }

    .title {
        text-align: center;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: .5px;
        margin: 0 0 6px 0;
        padding-bottom: 4px;
        border-bottom: 2px solid #000;
    }

    .meta td { border: none; padding: 1px 0; font-size: 8.5px; }

    .section-title {
        font-size: 9.5px;
        font-weight: bold;
        margin: 8px 0 3px 0;
        padding: 3px 6px;
        background: #333;
        color: #fff;
    }

    table.data { margin-bottom: 6px; }
    table.data th, table.data td {
        border: 0.6px solid #555;
        padding: 2.5px 3px;
        vertical-align: middle;
        word-wrap: break-word;
        font-size: 7.5px;
    }
    table.data thead th {
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
    }
    table.data thead { display: table-header-group; }
    table.data tr { page-break-inside: avoid; }

    .group td { background: #e9ecef; font-weight: bold; }
    .row-dirty td { background: #f1f1f1; }
    .row-alt td { background: #fafafa; }
    .row-dirty.row-alt td { background: #f1f1f1; }

    .text-center { text-align: center; }
    .muted { color: #666; font-style: italic; }
    .mark-clean { font-weight: bold; font-size: 9px; }
    .mark-dirty { font-weight: bold; font-size: 9px; }

    .verif-line { margin: 0 0 2px 0; }
    .verif-line:last-child { margin-bottom: 0; }
    .followup { border-top: 0.5px dashed #888; padding-top: 2px; margin-top: 2px; }

    .note-box { margin-top: 8px; font-size: 7.5px; }
    .note-box ul { list-style: none; padding-left: 0; margin: 2px 0; }
    .note-box ol { margin: 3px 0; padding-left: 16px; }

    .sign td { border: none; text-align: center; width: 33%; font-size: 8px; }
    </style>
</head>

<body>
@php
    $fallbackTime = $report->created_at ? $report->created_at->format('H:i') : null;
    $timeOf = function ($d) use ($fallbackTime) {
        return $d->verification_time ? substr($d->verification_time, 0, 5) : $fallbackTime;
    };

    $sortKey = function ($d) use ($timeOf) {
        return $timeOf($d) ?? '99:99'; // tanpa jam ke paling bawah
    };

    $reportTimes = $report->verification_times;
    $reportTimeText = count($reportTimes) ? implode(', ', $reportTimes) : ($fallbackTime ?? '-');

    $path = public_path('storage/image/logo.png');
    if (file_exists($path)) {
        $base64 = 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path));
    }

    $sections = [
        [
            'title'   => 'Pemeriksaan Ruangan',
            'col'     => 'Area Produksi / Elemen',
            'details' => $report->roomDetails,
            'group'   => fn ($d) => optional($d->room)->name ?? '-',
            'item'    => fn ($d) => optional($d->element)->element_name,
            'whole'   => 'Keseluruhan ruangan',
        ],
        [
            'title'   => 'Pemeriksaan Mesin & Peralatan',
            'col'     => 'Peralatan / Part',
            'details' => $report->equipmentDetails,
            'group'   => fn ($d) => optional($d->equipment)->name ?? '-',
            'item'    => fn ($d) => optional($d->part)->part_name,
            'whole'   => 'Keseluruhan mesin / peralatan',
        ],
    ];
@endphp

<div class="header" style="height: 60px;">
    <img src="{{ $base64 ?? '' }}" style="position: absolute; left: 0; top: 0; width: 50px;">
    <div style="position: absolute; left: 58px; top: 2px; font-size: 9px; font-weight: bold; line-height: 1.3;">
        PT. CHAROEN POKPHAND INDONESIA<br>
        FOOD DIVISION<br>
        {{ strtoupper($report->area->name ?? '') }} - INDONESIA
    </div>
    <div style="position: absolute; right: 0; top: 2px; font-size: 9px; font-weight: bold;">
        {{ $formNumber ?? '-' }}
    </div>
</div>

    <h3 class="title">Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</h3>

    <table class="meta">
        <tr>
            <td style="width: 50%;">
                Hari/Tanggal :
                <strong>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}</strong>
            </td>
            <td style="text-align: right;">
                Jam Verifikasi : <strong>{{ $reportTimeText }}</strong>
            </td>
        </tr>
    </table>

    {{-- ===== TABEL ===== --}}
    @foreach ($sections as $s)
        <div class="section-title">{{ $s['title'] }}</div>
        <table class="data">
            <colgroup>
                <col style="width:1%">
                <col style="width:21%">
                <col style="width:6%">
                <col style="width:5%">
                <col style="width:5%">
                <col style="width:14%">
                <col style="width:14%">
                <col style="width:34%">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">{{ $s['col'] }}</th>
                    <th rowspan="2">Jam</th>
                    <th colspan="2">Kondisi</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">Tindakan Koreksi</th>
                    <th rowspan="2">Verifikasi Setelah Tindakan Koreksi</th>
                </tr>
                <tr>
                    <th>Bersih</th>
                    <th>Kotor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($s['details']->groupBy($s['group'])->sortBy(fn ($g) => $g->map($sortKey)->min()) as $groupName => $details)
                    <tr class="group">
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td colspan="7">{{ strtoupper($groupName) }}</td>
                    </tr>

                    @foreach ($details->sortBy($sortKey)->values() as $detail)
                        @php $itemName = $s['item']($detail); @endphp
                        <tr class="{{ $detail->condition === 'dirty' ? 'row-dirty' : '' }} {{ $loop->even ? 'row-alt' : '' }}">
                            <td></td>
                            <td>
                                @if ($itemName) {{ $itemName }} @else <span class="muted">{{ $s['whole'] }}</span> @endif
                            </td>
                            <td class="text-center">{{ $timeOf($detail) ?? '-' }}</td>
                            <td class="text-center">
                                @if ($detail->condition === 'clean') <span class="mark-clean">&#10004;</span> @endif
                            </td>
                            <td class="text-center">
                                @if ($detail->condition === 'dirty') <span class="mark-dirty">X</span> @endif
                            </td>
                            <td>{{ $detail->notes }}</td>
                            <td>{{ $detail->corrective_action }}</td>
                            <td>
                                <div class="verif-line">
                                    <strong>Utama:</strong> {{ $detail->verification ?? '-' }}
                                </div>
                                @foreach ($detail->followups as $index => $followup)
                                    <div class="verif-line followup">
                                        <strong>Lanjutan #{{ $index + 1 }}:</strong>
                                        {{ $followup->verification ?? '-' }}
                                        | {{ $followup->notes ?: '-' }}
                                        | {{ $followup->corrective_action ?: '-' }}
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="text-center muted">Tidak ada data pemeriksaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div style="text-align: right; font-size: 7.5px; margin-top: 2px;">{{ $formNumber ?? '-' }}</div>

    {{-- ===== KETERANGAN ===== --}}
    <div class="note-box">
        <strong>Keterangan:</strong>
        <ul>
            <li>&#10004; : Bersih dan bebas material non halal</li>
            <li>X : Kotor, dengan kriteria:</li>
        </ul>
        <ol>
            <li>Berdebu</li>
            <li>Noda (karat, cat atau sejenisnya)</li>
            <li>Endapan kotoran</li>
            <li>Pertumbuhan mikroorganisme (jamur, bau busuk)</li>
            <li>Becek/menggenang</li>
        </ol>
    </div>

    {{-- ===== TANDA TANGAN ===== --}}
    <table class="sign" style="margin-top: 15px; page-break-inside: avoid;">
        <tr>
            <td>
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="60" style="margin: 6px 0;"><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>
            <td>
                Diketahui oleh:<br><br>
                @if ($report->known_by)
                    <img src="{{ $knownQr }}" width="60" style="margin: 6px 0;"><br>
                    <strong>{{ $report->known_by }}</strong><br>
                @else
                    <div style="height: 72px;"></div>
                    <strong>-</strong><br>
                @endif
                SPV/Foreman/Lady Produksi
            </td>
            <td>
                Disetujui oleh:<br><br>
                @if ($report->approved_by)
                    <img src="{{ $approvedQr }}" width="60" style="margin: 6px 0;"><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height: 72px;"></div>
                    <strong>-</strong><br>
                @endif
                Supervisor QC
            </td>
        </tr>
    </table>

</body>

</html>