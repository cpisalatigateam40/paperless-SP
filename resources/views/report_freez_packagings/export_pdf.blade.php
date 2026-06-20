<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Proses Pembekuan</title>

    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        line-height: 1.25;
        margin: 20px;
    }

    h3 {
        text-align: center;
        margin: 2px 0 8px 0;
        font-size: 12px;
    }

    .section-title {
        font-weight: bold;
        margin-top: 2rem;
        margin-bottom: 2px;
    }

    table.info {
        width: 100%;
        border-collapse: collapse;
    }

    table.info td {
        padding: 1px 2px;
        vertical-align: top;
    }

    .page-break {
        page-break-after: always;
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

    .no-border {
        border: none !important;
    }

    @page {
        margin-top: 65px;
        margin-bottom: 45px;
        margin-left: 45px;
        margin-right: 45px;
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

    @foreach($report->details as $detail)

    @php
    $actualTemps = $detail->freezing && $detail->freezing->actualTemps->count()
    ? $detail->freezing->actualTemps
    ->pluck('actual_temp')
    ->map(fn($t) => number_format($t, 2))
    ->implode(' / ')
    : '-';

    $beratAktual = collect([
    $detail->kartoning->weight_1 ?? null,
    $detail->kartoning->weight_2 ?? null,
    $detail->kartoning->weight_3 ?? null,
    $detail->kartoning->weight_4 ?? null,
    $detail->kartoning->weight_5 ?? null,
    ])->filter()->implode(' / ');
    @endphp

    <h3>
        VERIFIKASI PROSES PEMBEKUAN, PENGEMASAN SEKUNDER, DAN RELEASE PRODUK
    </h3>

    <div class="section-title">A. Informasi Produk</div>

    <table class="info">
        <tr>
            <td width="150">Hari, Tanggal</td>
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
            <td>{{ $detail->product->product_name ?? '-' }}</td>
        </tr>

        <tr>
            <td>Kode Produk</td>
            <td>:</td>
            <td>{{ $detail->production_code ?? '-' }}</td>
        </tr>

        <tr>
            <td>Gramasi</td>
            <td>:</td>
            <td>{{ $detail->gramase ?? '-' }} gr</td>
        </tr>
    </table>

    <div class="section-title">B. Hasil Verifikasi</div>

    <strong>Pembekuan</strong>

    <table class="info">
        <tr>
            <td width="150">Freezing</td>
            <td width="10">:</td>
            <td>{{ $detail->freezing->machine_type ?? '-' }}</td>
        </tr>

        <tr>
            <td>Machine</td>
            <td>:</td>
            <td>{{ $detail->freezing->iqf_machine ?? '-' }}</td>
        </tr>

        <tr>
            <td>Waktu Proses</td>
            <td>:</td>
            <td>
                {{ $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('H:i') : '-' }}
                s.d
                {{ $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('H:i') : '-' }}
            </td>
        </tr>

        <tr>
            <td>Std. suhu produk (°C)</td>
            <td>:</td>
            <td>{{ $detail->freezing->standard_temp ?? '-' }}</td>
        </tr>

        <tr>
            <td>Suhu aktual produk (°C)</td>
            <td>:</td>
            <td>{{ $actualTemps }}</td>
        </tr>

        <tr>
            <td>Suhu room IQF/ABF (°C)</td>
            <td>:</td>
            <td>{{ $detail->freezing->iqf_room_temp ?? '-' }}</td>
        </tr>

        <tr>
            <td>Notes</td>
            <td>:</td>
            <td>{{ $detail->freezing->notes ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <strong>Pengemasan Sekunder</strong>

    <table class="info">
        <tr>
            <td width="150">Kondisi Kemasan Sekunder</td>
            <td width="10">:</td>
            <td>{{ $detail->kartoning->carton_condition ?? '-' }}</td>
        </tr>

        <tr>
            <td>Label Kemasan Sekunder</td>
            <td>:</td>
            <td>{{ $detail->kartoning->label_condition ?? '-' }}</td>
        </tr>

        <tr>
            <td>Isi per Kemasan sekunder</td>
            <td>:</td>
            <td>{{ $detail->kartoning->content_bag ?? '-' }}</td>
        </tr>

        <tr>
            <td>Isi per Inner *RTG</td>
            <td>:</td>
            <td>{{ $detail->kartoning->content_rtg ?? '-' }}</td>
        </tr>

        <tr>
            <td>Isi per binded *prod. binded</td>
            <td>:</td>
            <td>{{ $detail->kartoning->content_binded ?? '-' }}</td>
        </tr>

        <tr>
            <td>Std. berat hasil kartoning</td>
            <td>:</td>
            <td>{{ $detail->kartoning->carton_weight_standard ?? '-' }}</td>
        </tr>

        <tr>
            <td>Berat aktual hasil kartoning</td>
            <td>:</td>
            <td>{{ $beratAktual }}</td>
        </tr>

        <tr>
            <td>Rata-rata</td>
            <td>:</td>
            <td>{{ $detail->kartoning->avg_weight ?? '-' }}</td>
        </tr>

        <tr>
            <td>Notes</td>
            <td>:</td>
            <td>{{ $detail->kartoning->notes ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <strong>Status Produk</strong>

    <table class="info">
        <tr>
            <td width="150">Release or Hold</td>
            <td width="10">:</td>
            <td>{{ $detail->release_status ?? '-' }}</td>
        </tr>

        <tr>
            <td>Tindakan Perbaikan</td>
            <td>:</td>
            <td>{{ $detail->corrective_action ?? '-' }}</td>
        </tr>

        <tr>
            <td>Notes</td>
            <td>:</td>
            <td>{{ $detail->notes ?? '-' }}</td>
        </tr>
    </table>

<div class="section-title">
    C. Catatan & Dokumentasi
</div>

<table class="info">
    <tr>
        <td width="150">Catatan Laporan</td>
        <td width="10">:</td>
        <td>{{ $report->notes ?? '-' }}</td>
    </tr>

    <tr>
        <td>Catatan Pembekuan</td>
        <td>:</td>
        <td>{{ $detail->freezing->notes ?? '-' }}</td>
    </tr>

    <tr>
        <td>Catatan Pengemasan Sekunder</td>
        <td>:</td>
        <td>{{ $detail->kartoning->notes ?? '-' }}</td>
    </tr>
</table>

<br>

<table style="width:100%; border:none;">
    <tr>
        {{-- KIRI: Dokumentasi Pembekuan --}}
        <td style="border:none; width:50%; vertical-align:top; padding-right:10px;">

            <strong>Dokumentasi Pembekuan</strong>

            @if($detail->documentations->count())

                @foreach($detail->documentations as $doc)

                    @php
                        $imagePath = storage_path('app/public/' . $doc->image);

                        $base64Image = null;

                        if (file_exists($imagePath)) {
                            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $data = file_get_contents($imagePath);
                            $base64Image = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp

                    @if($base64Image)

                        <div style="
                            margin-top:10px;
                            margin-bottom:10px;
                            text-align:left;
                        ">
                            <img src="{{ $base64Image }}"
                                style="
                                    width:100%;
                                    max-width:200px;
                                    height:auto;
                                    max-height:180px;
                                    border:1px solid #999;
                                ">

                            <div style="
                                font-size:9px;
                                margin-top:2px;
                                text-align:left;
                            ">
                                Dokumentasi
                            </div>
                        </div>

                    @endif

                @endforeach

            @else

                <div style="margin-top:5px;">
                    Tidak ada dokumentasi.
                </div>

            @endif

        </td>

        {{-- KANAN: Dokumentasi Kartoning --}}
        <td style="border:none; width:50%; vertical-align:top; padding-left:10px;">

            <strong>Dokumentasi Kartoning</strong>

            @if($detail->kartoningDocumentations->count())

                @foreach($detail->kartoningDocumentations as $doc)

                    @php
                        $imagePath = storage_path('app/public/' . $doc->image);

                        $base64Image = null;

                        if (file_exists($imagePath)) {
                            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $data = file_get_contents($imagePath);
                            $base64Image = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp

                    @if($base64Image)

                        <div style="
                            margin-top:10px;
                            margin-bottom:10px;
                            text-align:left;
                        ">
                            <img src="{{ $base64Image }}"
                                style="
                                    width:100%;
                                    max-width:200px;
                                    height:auto;
                                    max-height:180px;
                                    border:1px solid #999;
                                ">

                            <div style="
                                font-size:9px;
                                margin-top:2px;
                                text-align:left;
                            ">
                                Dokumentasi
                            </div>
                        </div>

                    @endif

                @endforeach

            @else

                <div style="margin-top:5px;">
                    Tidak ada dokumentasi.
                </div>

            @endif

        </td>
    </tr>
</table>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

    @endforeach

    <table style="width: 100%; border: none; margin-top:30px;">
        <tr>
            <td style="text-align: center; border: none; width: 33%;">
                Diperiksa oleh:<br><br>
                <img src="{{ $createdQr }}" width="80"><br><br>
                <strong>{{ $report->created_by }}</strong><br>
                QC Inspector
            </td>

            <td style="text-align: center; border: none; width: 33%;">
                Diketahui oleh:<br><br>

                @if($report->known_by)
                    <img src="{{ $knownQr }}" width="80"><br><br>
                    <strong>{{ $report->known_by }}</strong><br>
                @else
                    <div style="height:90px;"></div>
                    <strong>-</strong><br>
                @endif

                SPV/Foreman/Lady Produksi
            </td>

            <td style="text-align: center; border: none; width: 33%;">
                Disetujui oleh:<br><br>

                @if($report->approved_by)
                    <img src="{{ $approvedQr }}" width="80"><br><br>
                    <strong>{{ $report->approved_by }}</strong><br>
                @else
                    <div style="height:90px;"></div>
                    <strong>-</strong><br>
                @endif

                Supervisor QC
            </td>
        </tr>
    </table>

</body>

</html>