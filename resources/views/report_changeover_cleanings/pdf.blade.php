<!DOCTYPE html>
<html>

<head>
    <title>Pemeriksaan Kebersihan Setelah Pergantian Produk</title>
    <style>
    @font-face {
        font-family: "DejaVu Sans";
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format("truetype");
    }

    @page {
        size: F4 landscape;
        margin: 55px 25px 25px 25px;
    }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 9px;
        margin: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 5px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: middle;
    }

    th {
        font-weight: bold;
    }

    .text-start {
        text-align: left;
    }

    .no-border {
        border: none !important;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    .header {
        position: fixed;
        top: -45px;
        left: 0;
        width: 100%;
        border: none;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    h3 {
        margin: 0 0 5px 0;
        padding: 0;
        font-size: 11px;
        text-align: center;
    }

    .signature-table {
        width: 100%;
        border: none;
        margin-top: 5px;
        page-break-inside: avoid;
    }

    .signature-table td {
        border: none;
        text-align: center;
    }

    .signature-table img {
        width: 55px;
    }

    .no-page-break {
        page-break-inside: avoid;
    }
    </style>
</head>

<body>
    {{-- HEADER --}}
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

                                        $base64 =
                                            'data:image/' .
                                            $type .
                                            ';base64,' .
                                            base64_encode($data);
                                    }
                                @endphp

                                <img src="{{ $base64 ?? '' }}"
                                    style="width: 50px;">
                            </td>

                            <td class="no-border"
                                style="vertical-align: middle; padding-left: 10px;">

                                <div style="font-size: 9px;
                                            font-weight: bold;
                                            line-height: 1.2;">

                                    CHAROEN<br>
                                    POKPHAND<br>
                                    INDONESIA PT.<br>
                                    Food Division

                                </div>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="text-align: center;">PEMERIKSAAN KEBERSIHAN SETELAH PERGANTIAN PRODUK</h3>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td class="text-start" style="border: none;">
                Hari/Tanggal:
                <span style="text-decoration: underline;">
                    {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d/m/Y') }}
                </span>
            </td>
            <td class="text-start" style="border: none;">
                Shift: <span style="text-decoration: underline;"> {{ $report->shift }} </span>
            </td>
            <td class="text-start" style="border: none;">
                Area: <span style="text-decoration: underline;"> {{ $report->area->name ?? '-' }} </span>
            </td>
        </tr>
    </table>

    @php
        // Susun matriks: kolom = batch (produk+jam unik), baris = item
        $batches = [];
        $matrix  = [];

        foreach ($report->details as $d) {
            $batchKey = $d->product_uuid . '|' . $d->time;

            if (!isset($batches[$batchKey])) {
                $batches[$batchKey] = [
                    'product_name' => $d->product->product_name ?? '-',
                    'time'         => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                ];
            }

            $matrix[$d->item_uuid][$batchKey] = $d;
        }

        $reportItems = $report->details
            ->groupBy('item_uuid')
            ->map(fn ($group) => $group->first()->item)
            ->filter()
            ->sortBy([['category', 'asc'], ['name', 'asc']])
            ->values();

        $colCount = 2 + (count($batches) * 2) + 2;
    @endphp

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Item</th>
                @foreach($batches as $batch)
                <th colspan="2">
                    {{ $batch['product_name'] }}<br>
                    Jam: {{ $batch['time'] }}
                </th>
                @endforeach
                <th rowspan="2">Keterangan</th>
                <th rowspan="2">Tindakan Koreksi</th>
            </tr>
            <tr>
                @foreach($batches as $batch)
                <th>X/✓</th>
                <th>Penjelasan</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $reportItems->groupBy('category');
                $no = 1;
            @endphp

            @forelse($groupedItems as $category => $itemsByCategory)

                <tr>
                    <td colspan="{{ $colCount }}"
                        style="font-weight:bold; background:#d9d9d9; text-align:left;">
                        {{ $category }}
                    </td>
                </tr>

                @foreach($itemsByCategory as $item)
                @php
                    $rowKeterangan = [];
                    $rowTindakan = [];
                @endphp

                <tr>
                    <td>{{ $no++ }}</td>

                    <td class="text-start">
                        &nbsp;&nbsp;&nbsp;{{ $item->name }}
                    </td>

                    @foreach($batches as $batchKey => $batch)

                        @php
                            $cell = $matrix[$item->uuid][$batchKey] ?? null;
                        @endphp

                        <td>
                            {{ $cell->result ?? '-' }}
                        </td>

                        <td class="text-start">
                            {{ $cell->explanation ?? '-' }}
                        </td>

                        @php
                            if ($cell) {
                                if ($cell->notes) {
                                    $rowKeterangan[] = $cell->notes;
                                }

                                if ($cell->corrective_action) {
                                    $rowTindakan[] = $cell->corrective_action;
                                }
                            }
                        @endphp

                    @endforeach

                    <td class="text-start">
                        {{ $rowKeterangan ? implode('; ', $rowKeterangan) : '-' }}
                    </td>

                    <td class="text-start">
                        {{ $rowTindakan ? implode('; ', $rowTindakan) : '-' }}
                    </td>
                </tr>

                @endforeach

            @empty

                <tr>
                    <td colspan="{{ $colCount }}">
                        Belum ada detail
                    </td>
                </tr>

            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="text-align: right; border: none;">QM 38/00</td>
        </tr>
    </table>

    <table class="signature-table no-page-break">
        <tr>
            <td style="width:33%;">
                Dilaporkan Oleh:<br><br>

                <img src="{{ $createdQr }}" width="55"><br><br>

                <strong>{{ $report->created_by }}</strong>
            </td>

            <td style="width:33%;">
                Diketahui Oleh:<br><br>

                @if($report->known_by)
                <img src="{{ $knownQr }}" width="55"><br><br>

                <strong>{{ $report->known_by }}</strong>
                @else
                <br><br><br><br><br>
                <strong>-</strong>
                @endif
            </td>

            <td style="width:33%;">
                Disetujui Oleh:<br><br>

                @if($report->approved_by)
                <img src="{{ $approvedQr }}" width="55"><br><br>

                <strong>{{ $report->approved_by }}</strong>
                @else
                <br><br><br><br><br>
                <strong>-</strong>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>