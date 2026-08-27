<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi Penerapan GMP Karyawan & Sanitasi Area</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        margin-top: 30px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 6px;
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 3px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    .text-center { text-align: center; }
    .no-border { border: none !important; }
    .mb-2 { margin-bottom: 1rem; }
    .mb-3 { margin-bottom: 1.5rem; }
    .fw-bold { font-weight: bold; }

    .section-title {
        font-weight: bold;
        margin-bottom: 4px;
    }

    .info-table td {
        border: none;
        padding: 1px 0;
    }

    .notes-text {
        font-size: 8px;
        font-style: italic;
        margin-top: -2px;
        margin-bottom: 10px;
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

    ul { margin: unset; padding: .5rem; }
    li { list-style-type: none; }
    tr, td, th { page-break-inside: avoid; }
    thead { display: table-header-group; }
    </style>
</head>

<body>

    {{-- header --}}
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

    <h3 class="mb-2 text-center" style="text-transform: uppercase;">Verifikasi Penerapan GMP Karyawan &amp; Sanitasi Area</h3>

    @php
        $sectionLabel = $report->section === 'gmp_karyawan' ? 'Penerapan GMP Karyawan' : 'Sanitasi Area';
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
    @endphp

    {{-- ===== A. INFORMASI PRODUK ===== --}}
    <div class="section-title">A. Informasi Produk</div>
    <table class="info-table mb-3" style="width: 60%;">
        <tr>
            <td width="100">Hari, Tanggal</td>
            <td width="15">:</td>
            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>:</td>
            <td>{{ $report->shift }}</td>
        </tr>
        <tr>
            <td>Verifikasi</td>
            <td>:</td>
            <td>{{ $sectionLabel }}</td>
        </tr>
    </table>

    {{-- ===== B. HASIL VERIFIKASI ===== --}}
    <div class="section-title">B. Hasil Verifikasi {{ $sectionLabel }}</div>

    @foreach($report->waktuPemeriksaans as $wIndex => $waktu)

    <table style="width: 100%; border: none; margin-bottom: 2px;">
        <tr style="border: none;">
            <td class="no-border fw-bold" style="width: 30%;">
                {{ $roman[$wIndex] ?? ($wIndex + 1) }}. Waktu Pemeriksaan
            </td>
            <td class="no-border">
                : {{ $waktu->jam_pemeriksaan ? \Carbon\Carbon::parse($waktu->jam_pemeriksaan)->format('H:i') : '-' }} WIB
            </td>
        </tr>
    </table>

    @if($report->section === 'gmp_karyawan')
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Area</th>
                <th rowspan="2">Nama Karyawan</th>
                <th colspan="8">Penerapan GMP Karyawan</th>
                <th rowspan="2">Tindakan<br>Koreksi</th>
            </tr>
            <tr>
                <th>Seragam &amp; APD<br>lengkap</th>
                <th>Sarung tangan<br>utuh</th>
                <th>Sepatu boots<br>bersih</th>
                <th>Tidak pakai<br>perhiasan &amp; jam tangan</th>
                <th>Kuku &amp; tangan<br>bersih, tanpa luka</th>
                <th>Kuku tidak panjang<br>&amp; tidak cat kuku</th>
                <th>Perilaku &amp;<br>kebiasaan kerja</th>
                <th>Potensi cross<br>contamination</th>
            </tr>
        </thead>
        <tbody>
            @forelse($waktu->employeeChecks as $j => $emp)
            <tr>
                <td class="text-center">{{ $j + 1 }}</td>
                <td>{{ $emp->section->section_name ?? '-' }}</td>
                <td>{{ $emp->employee_name }}</td>
                @foreach(['seragam_apd_lengkap','sarung_tangan_utuh','sepatu_boots_bersih','tidak_pakai_perhiasan','kuku_tangan_bersih','kuku_tidak_panjang','perilaku_kerja','potensi_cross_contamination'] as $field)
                <td class="text-center">
                    @if(is_null($emp->$field)) -
                    @elseif($emp->$field) Ok
                    @else Tidak OK
                    @endif
                </td>
                @endforeach
                <td>{{ $emp->tindakan_koreksi ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="notes-text">Notes: {{ $waktu->catatan ?: '-' }}</p>
    @else
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Area</th>
                <th rowspan="2">Item Verifikasi</th>
                <th rowspan="2">Std.<br>klorin</th>
                <th colspan="4">Sanitasi Area</th>
            </tr>
            <tr>
                <th>Kadar klorin<br>(ppm)</th>
                <th>Suhu<br>(°C)</th>
                <th>Tindakan<br>Koreksi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($waktu->sanitationChecks as $j => $san)
            <tr>
                <td class="text-center">{{ $j + 1 }}</td>
                <td>{{ $san->section->section_name ?? '-' }}</td>
                <td>{{ $san->item_verifikasi }}</td>
                <td class="text-center">{{ $san->standar_klorin ?? '-' }}</td>
                <td class="text-center">{{ $san->kadar_klorin ?? '-' }}</td>
                <td class="text-center">{{ $san->suhu ?? '-' }}</td>
                <td>{{ $san->tindakan_koreksi ?: '-' }}</td>
                <td>{{ $san->keterangan ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="notes-text">Catatan: {{ $waktu->catatan ?: '-' }}</p>
    @endif

    @endforeach

    <br>

    {{-- ===== TANDA TANGAN ===== --}}
    <table style="width: 100%; border: none; margin-top: 2rem;">
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
                Foreman/SPV Produksi
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