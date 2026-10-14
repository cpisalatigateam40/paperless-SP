@php
    $SHOWERING_PROCESS = 'Showering & Cooling Down';

    $minutes = fn($start, $end) => ($start && $end)
        ? \Carbon\Carbon::parse($start)->diffInMinutes(\Carbon\Carbon::parse($end))
        : null;

    $fmt = fn($t) => $t ? \Carbon\Carbon::parse($t)->format('H:i') : '';
@endphp

{{-- Partial untuk tabel parameter, dipakai 3x --}}
@php
    $stepTable = function ($steps) {
        $html = '<div class="table-responsive mb-3"><table class="table table-bordered table-sm text-center align-middle">'
            . '<thead class="thead-light"><tr>'
            . '<th>Parameter</th><th>Setting Suhu (°C)</th><th>Aktual Suhu (°C)</th>'
            . '<th>Setup Time</th><th>Actual Time</th><th>Setting RH (%)</th>'
            . '<th>Aktual RH (%)</th><th>Setting Core</th><th>Aktual Core</th>'
            . '</tr></thead><tbody>';

        if ($steps->isEmpty()) {
            $html .= '<tr><td colspan="9" class="text-muted">Belum ada data</td></tr>';
        }

        foreach ($steps as $s) {
            $html .= '<tr>';
            foreach (['process_name', 'setting_temp', 'actual_temp', 'setting_time', 'actual_time',
                      'setting_rh', 'actual_rh', 'setting_ct', 'actual_ct'] as $f) {
                $html .= '<td>' . e($s->{$f}) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    };
@endphp

@foreach ($details as $detail)
    @php
        $cookingSteps   = $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS);
        $showeringSteps = $detail->steps->where('process_name', $SHOWERING_PROCESS);
        $duration       = $minutes($detail->start_process, $detail->end_process);
    @endphp

    <div class="card mb-3">
        <div class="card-body">

            {{-- A. INFORMASI PRODUK --}}
            <h6 class="font-weight-bold">A. Informasi Produk</h6>
            <table class="table table-borderless table-sm mb-3" style="max-width: 500px;">
                <tr>
                    <td width="180">Hari, Tanggal</td><td width="20">:</td>
                    <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr><td>Shift</td><td>:</td><td>{{ $report->shift }}</td></tr>
                <tr><td>Nama Produk</td><td>:</td><td>{{ $detail->product->product_name ?? '-' }}</td></tr>
                <tr><td>Kode Produk</td><td>:</td><td>{{ $detail->production_code }}</td></tr>
                <tr><td>Gramasi</td><td>:</td><td>{{ $detail->gramase }} gr</td></tr>
                <tr><td>Smoke House</td><td>:</td><td>{{ $detail->machine_name }}</td></tr>
            </table>

            {{-- B. HASIL VERIFIKASI COOKING --}}
            <h6 class="font-weight-bold">B. Hasil Verifikasi Cooking</h6>
            <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                <tr><td width="180">Nomor Smoke House</td><td width="20">:</td><td>{{ $detail->smoke_house_no }}</td></tr>
                <tr><td>Jumlah Trolley</td><td>:</td><td>{{ $detail->trolley_count }} trolly</td></tr>
                <tr><td>Jumlah Stick/Trolley</td><td>:</td><td>{{ $detail->stick_count }} stick</td></tr>
                <tr>
                    <td>Waktu Proses</td><td>:</td>
                    <td>
                        {{ $fmt($detail->start_process) }} - {{ $fmt($detail->end_process) }}
                        @if ($duration !== null) ({{ $duration }} menit) @endif
                    </td>
                </tr>
            </table>

            {!! $stepTable($cookingSteps) !!}

            @if ($detail->sensories)
                <div class="mb-3">
                    <strong>Hasil Sensori:</strong>
                    <ol class="mb-1">
                        <li>Kenampakan : {{ $detail->sensories->appearance ?: '-' }}</li>
                        <li>Warna : {{ $detail->sensories->color ?: '-' }}</li>
                        <li>Aroma : {{ $detail->sensories->aroma ?: '-' }}</li>
                        <li>Rasa : {{ $detail->sensories->taste ?: '-' }}</li>
                        <li>Tekstur : {{ $detail->sensories->texture ?: '-' }}</li>
                    </ol>
                    <small class="text-muted">Notes: {{ $detail->sensories->notes ?: '-' }}</small>
                </div>
            @endif

            {{-- COOKING ULANG --}}
            @foreach ($detail->reworks as $rework)
                @php $reworkDuration = $minutes($rework->start_process, $rework->end_process); @endphp

                <hr>
                <h6 class="font-weight-bold text-warning">Cooking Ulang</h6>
                <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                    <tr><td width="180">Nomor Smoke House</td><td width="20">:</td><td>{{ $rework->smoke_house_no }}</td></tr>
                    <tr><td>Jumlah Trolley</td><td>:</td><td>{{ $rework->trolley_count }} trolly</td></tr>
                    <tr><td>Jumlah Stick/Trolley</td><td>:</td><td>{{ $rework->stick_count }} stick</td></tr>
                    <tr>
                        <td>Waktu Proses</td><td>:</td>
                        <td>
                            {{ $fmt($rework->start_process) }} - {{ $fmt($rework->end_process) }}
                            @if ($reworkDuration !== null) ({{ $reworkDuration }} menit) @endif
                        </td>
                    </tr>
                </table>

                {!! $stepTable($rework->steps) !!}
            @endforeach

            {{-- C. SHOWERING & COOLING DOWN --}}
            <hr>
            <h6 class="font-weight-bold">C. Hasil Verifikasi Showering & Cooling Down</h6>
            {!! $stepTable($showeringSteps) !!}

            <p class="mb-0 mt-3">
                Proses Cooling Down Selesai :
                <strong>{{ $fmt($detail->cooling_finish) }}</strong>
            </p>
        </div>
    </div>
@endforeach

{{-- D. CATATAN (level report) --}}
<div class="card mb-2">
    <div class="card-body">
        <h6 class="font-weight-bold">D. Catatan &amp; Dokumentasi</h6>
        <p class="mb-0">{{ $report->notes ?: '-' }}</p>
    </div>
</div>