<table>
    <tr>
        <td colspan="12" style="font-weight:bold; font-size:14px;">
            VERIFIKASI PROSES PEMASAKAN DI SMOKE HOUSE
        </td>
    </tr>
    <tr>
        <td colspan="12">Periode: {{ $periodLabel }}</td>
    </tr>
    <tr>
        <td colspan="12"></td>
    </tr>

    @php
    $SHOWERING_PROCESS = 'Showering & Cooling Down';
    @endphp

    @forelse($reports as $report)
    @foreach($report->details as $detail)

    @php
    $cookingSteps = $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS);
    $showeringSteps = $detail->steps->where('process_name', '==', $SHOWERING_PROCESS);
    @endphp

    {{-- ===== INFO PRODUK ===== --}}
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Hari, Tanggal</td>
        <td colspan="10">{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Shift</td>
        <td colspan="10">{{ $report->shift }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Nama Produk</td>
        <td colspan="10">{{ $detail->product->product_name ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Kode Produk</td>
        <td colspan="10">{{ $detail->production_code }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Gramasi</td>
        <td colspan="10">{{ $detail->gramase }} gr</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Smoke House</td>
        <td colspan="10">{{ $detail->machine_name }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Nomor Smoke House</td>
        <td colspan="10">{{ $detail->smoke_house_no }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Jumlah Trolley</td>
        <td colspan="10">{{ $detail->trolley_count }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Jumlah Stick/Trolley</td>
        <td colspan="10">{{ $detail->stick_count }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Waktu Proses</td>
        <td colspan="10">
            {{ optional($detail->start_process)->format('H:i') }} -
            {{ optional($detail->end_process)->format('H:i') }}
        </td>
    </tr>
    <tr>
        <td colspan="12"></td>
    </tr>

    {{-- ===== VERIFIKASI COOKING ===== --}}
    <tr>
        <td colspan="12" style="font-weight:bold;">B. Hasil Verifikasi Cooking</td>
    </tr>
    <tr style="font-weight:bold; background-color:#EEE;">
        <td>Parameter</td>
        <td>Setting Suhu</td>
        <td>Aktual Suhu</td>
        <td>Setup Time</td>
        <td>Actual Time</td>
        <td>Setting RH</td>
        <td>Aktual RH</td>
        <td>Setting Core</td>
        <td>Aktual Core</td>
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
        <td colspan="9">Belum ada data</td>
    </tr>
    @endforelse

    @if($detail->sensories)
    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="12" style="font-weight:bold;">Hasil Sensori</td>
    </tr>
    <tr>
        <td>Kenampakan</td>
        <td>{{ $detail->sensories->appearance ?: '-' }}</td>
    </tr>
    <tr>
        <td>Warna</td>
        <td>{{ $detail->sensories->color ?: '-' }}</td>
    </tr>
    <tr>
        <td>Aroma</td>
        <td>{{ $detail->sensories->aroma ?: '-' }}</td>
    </tr>
    <tr>
        <td>Rasa</td>
        <td>{{ $detail->sensories->taste ?: '-' }}</td>
    </tr>
    <tr>
        <td>Tekstur</td>
        <td>{{ $detail->sensories->texture ?: '-' }}</td>
    </tr>
    <tr>
        <td>Notes</td>
        <td colspan="11">{{ $detail->sensories->notes ?: '-' }}</td>
    </tr>
    @endif

    {{-- ===== COOKING ULANG ===== --}}
    @foreach($detail->reworks as $rework)
    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="12" style="font-weight:bold;">Cooking Ulang</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Nomor Smoke House</td>
        <td colspan="10">{{ $rework->smoke_house_no }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Jumlah Trolley</td>
        <td colspan="10">{{ $rework->trolley_count }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Jumlah Stick/Trolley</td>
        <td colspan="10">{{ $rework->stick_count }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Waktu Proses</td>
        <td colspan="10">
            {{ optional($rework->start_process)->format('H:i') }} -
            {{ optional($rework->end_process)->format('H:i') }}
        </td>
    </tr>
    <tr style="font-weight:bold; background-color:#EEE;">
        <td>Parameter</td>
        <td>Setting Suhu</td>
        <td>Aktual Suhu</td>
        <td>Setup Time</td>
        <td>Actual Time</td>
        <td>Setting RH</td>
        <td>Aktual RH</td>
        <td>Setting Core</td>
        <td>Aktual Core</td>
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
        <td colspan="9">Belum ada data</td>
    </tr>
    @endforelse
    @endforeach

    {{-- ===== SHOWERING & COOLING DOWN ===== --}}
    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="12" style="font-weight:bold;">C. Hasil Verifikasi Showering & Cooling Down</td>
    </tr>
    <tr style="font-weight:bold; background-color:#EEE;">
        <td>Parameter</td>
        <td>Setting Suhu</td>
        <td>Aktual Suhu</td>
        <td>Setup Time</td>
        <td>Actual Time</td>
        <td>Setting RH</td>
        <td>Aktual RH</td>
        <td>Setting Core</td>
        <td>Aktual Core</td>
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
        <td colspan="9">Belum ada data</td>
    </tr>
    @endforelse

    <tr>
        <td colspan="2" style="font-weight:bold; background-color:#DDD;">Proses Cooling Down Selesai</td>
        <td colspan="10">{{ optional($detail->cooling_finish)->format('H:i') }} WIB</td>
    </tr>

    {{-- ===== CATATAN ===== --}}
    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="12" style="font-weight:bold;">D. Catatan & Dokumentasi</td>
    </tr>
    <tr>
        <td colspan="12">{{ $report->notes ?: '-' }}</td>
    </tr>

    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="12" style="border-bottom: 2px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12"></td>
    </tr>

    @endforeach
    @empty
    <tr>
        <td colspan="12">Tidak ada data pada periode ini.</td>
    </tr>
    @endforelse
</table>