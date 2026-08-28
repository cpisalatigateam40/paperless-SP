<!DOCTYPE html>
<html>

<head>
    <title>Pemeriksaan Kebersihan Setelah Change-Over</title>
    <style>
    @font-face {
        font-family: "DejaVu Sans";
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format("truetype");
    }

    @page {
        size: A4 portrait;
        margin: 60px 25px 25px 25px;
    }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 9px;
        margin: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 6px;
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: middle;
    }

    th { font-weight: bold; }
    .text-start { text-align: left; }
    .no-border { border: none !important; }

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

    h3 {
        margin: 0 0 8px 0;
        padding: 0;
        font-size: 12px;
        text-align: center;
    }

    .section-title {
        font-weight: bold;
        margin: 8px 0 3px 0;
        font-size: 9.5px;
    }

    .info-table td { border: none; text-align: left; padding: 1px 3px; }
    .info-label { width: 140px; }

    .group-header td {
        font-weight: bold;
        background: #e6e6e6;
        text-align: left;
    }

    .keterangan-box {
        font-size: 8px;
        margin-top: 6px;
    }
    .keterangan-box p { margin: 2px 0; }

    .signature-table {
        width: 100%;
        border: none;
        margin-top: 10px;
        page-break-inside: avoid;
    }
    .signature-table td { border: none; text-align: center; }
    .signature-table img { width: 55px; }

    .page-break { page-break-after: always; }
    .no-page-break { page-break-inside: avoid; }
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
                <td class="no-border" style="width: 40%; text-align: right; vertical-align: middle; font-size: 9px;">
                    {{ $formNumber ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    @foreach($pages as $index => $page)

        <h3 style="margin-top: 2rem;">PEMERIKSAAN KEBERSIHAN SETELAH CHANGE-OVER</h3>

        {{-- A. INFORMASI PRODUK --}}
        <div class="section-title">A. Informasi Produk</div>
        <table class="info-table">
            <tr>
                <td class="info-label">Hari, Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="info-label">Shift</td>
                <td>: {{ $report->shift }}</td>
            </tr>
            <tr>
                <td class="info-label">Produk yang akan diproduksi</td>
                <td>: {{ $page['product']->product_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Kode produksi</td>
                <td>: {{ $page['production_code'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Area</td>
                <td>: {{ $report->area->name ?? '-' }}{{ $page['section_names'] ? ' (' . $page['section_names'] . ')' : '' }}</td>
            </tr>
        </table>

        {{-- B. HASIL VERIFIKASI --}}
        <div class="section-title">B. Hasil Verifikasi Kebersihan Setelah Change-Over</div>
        <table class="info-table" style="margin-bottom: 3px;">
            <tr>
                <td class="info-label">Waktu Pemeriksaan</td>
                <td>: {{ $page['time'] }} WIB</td>
            </tr>
        </table>

        @php
            $criteriaPairs = [[1, 2], [3, 4], [5, 6], [7, 8]];

            $groups = [
                'sisa_bahan'      => ['label' => 'SISA BAHAN DAN KEMASAN', 'rows' => $page['sisa_bahan']],
                'mesin_peralatan' => ['label' => 'MESIN DAN PERALATAN (menyesuaikan dengan area yang dipilih)', 'rows' => $page['mesin_peralatan']],
                'kondisi_ruangan' => ['label' => 'KONDISI RUANGAN', 'rows' => $page['kondisi_ruangan']],
            ];
        @endphp

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width:22px;">No</th>
                    <th rowspan="2" style="width:150px;">Item</th>
                    <th colspan="4">Penilaian Kondisi Bahan/Peralatan</th>
                    <th rowspan="2" style="width:100px;">Tindakan koreksi</th>
                    <th rowspan="2" style="width:100px;">Keterangan</th>
                </tr>
                <tr>
                    @foreach($criteriaPairs as $pair)
                    <th style="width:32px;">{{ $pair[0] }}/{{ $pair[1] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    <tr class="group-header">
                        <td colspan="8">{{ $group['label'] }}</td>
                    </tr>

                    @forelse($group['rows'] as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="text-start">{{ $row['name'] }}</td>

                        @foreach($criteriaPairs as $pair)
                        <td>
                            @if($row['score'] && in_array($row['score'], $pair))
                                v
                            @endif
                        </td>
                        @endforeach

                        <td class="text-start">{{ $row['corrective_action'] ?? '-' }}</td>
                        <td class="text-start">{{ $row['notes'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">-</td>
                    </tr>
                    @endforelse
                @endforeach
            </tbody>
        </table>

        <div class="keterangan-box no-page-break">
            <strong>Keterangan Pengecekan :</strong>
            <p>- Pengecekan Kondisi Sisa Bahan/Kemasan: nomor 1-8</p>
            <p>- Pengecekan Kondisi Mesin dan Peralatan: nomor 3-8</p>
            <p>- Pengecekan Kondisi Ruangan: nomor 3-8</p>

            <strong>Kriteria Penilaian :</strong>
            <table style="border: none; margin-top: 3px;">
                <tr>
                    <td class="no-border text-start" style="width:50%;">1. Bersih, tidak ada sisa bahan/kemasan sebelumnya;</td>
                    <td class="no-border text-start">2. Ada sisa bahan/kemasan sebelumnya</td>
                </tr>
                <tr>
                    <td class="no-border text-start">3. Bebas dari kontaminasi dan bahan sebelumnya;</td>
                    <td class="no-border text-start">4. Ada kontaminasi atau sisa bahan sebelumnya</td>
                </tr>
                <tr>
                    <td class="no-border text-start">5. Bebas dari potensi kontaminasi allergen;</td>
                    <td class="no-border text-start">6. Ada potensi kontaminasi allergen</td>
                </tr>
                <tr>
                    <td class="no-border text-start">7. Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang;</td>
                    <td class="no-border text-start">8. Tidak bersih, ada kontaminan/kotoran</td>
                </tr>
            </table>
        </div>

        <table class="signature-table no-page-break" style="margin-top: 3rem;">
            <tr>
                <td style="width:33%;">
                    Diperiksa oleh,<br><br>
                    <img src="{{ $createdQr }}" width="55"><br><br>
                    <strong>{{ $report->created_by }}</strong><br>
                    <small>QC Inspector</small>
                </td>

                <td style="width:33%;">
                    Diketahui oleh,<br><br>
                    @if($report->known_by)
                        <img src="{{ $knownQr }}" width="55"><br><br>
                        <strong>{{ $report->known_by }}</strong>
                    @else
                        <br><br><br><br><br>
                        <strong>-</strong>
                    @endif
                    <br><small>Foreman/SPV Produksi</small>
                </td>

                <td style="width:33%;">
                    Disetujui oleh,<br><br>
                    @if($report->approved_by)
                        <img src="{{ $approvedQr }}" width="55"><br><br>
                        <strong>{{ $report->approved_by }}</strong>
                    @else
                        <br><br><br><br><br>
                        <strong>-</strong>
                    @endif
                    <br><small>Supervisor QC</small>
                </td>
            </tr>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach
</body>

</html>