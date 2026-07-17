<div class="p-3 bg-light border-top">

    {{-- A. INFORMASI PRODUK --}}
    <h6 class="fw-bold">A. Informasi Produk</h6>
    <table class="table table-sm table-borderless mb-3" style="max-width: 500px;">
        <tr>
            <td width="160">Hari, Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Shift</td>
            <td>: {{ $report->shift }}</td>
        </tr>
        <tr>
            <td>Nama Produk</td>
            <td>: {{ $report->product->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kode Produk</td>
            <td>: {{ $report->product_code_range ?? '-' }}</td>
        </tr>
        <tr>
            <td>Gramasi</td>
            <td>: {{ $report->gramase }} gr</td>
        </tr>
    </table>

    {{-- B. HASIL VERIFIKASI COOKING --}}
    <h6 class="fw-bold">B. Hasil Verifikasi Cooking</h6>

    @forelse ($report->batches as $bIndex => $batch)
        <div class="border rounded p-2 mb-3 bg-white">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Nomor Steamer:</strong> {{ $batch->steamer_number }}</div>
                <div class="col-md-3"><strong>Jumlah Trolly:</strong> {{ $batch->trolley_count ?? '-' }} trolly</div>
                <div class="col-md-3"><strong>Tray/Trolly:</strong> {{ $batch->tray_per_trolley ?? '-' }} tray/trolly</div>
                <div class="col-md-3">
                    <strong>Waktu Proses:</strong>
                    {{ $batch->start_time ?? '-' }} - {{ $batch->end_time ?? '-' }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">Kode Produksi</th>
                            <th rowspan="2" class="align-middle">Start Process</th>
                            <th rowspan="2" class="align-middle">End Process</th>
                            <th rowspan="2" class="align-middle">Setup Time</th>
                            <th rowspan="2" class="align-middle">Suhu Ruang (°C)</th>
                            <th colspan="{{ max(1, $batch->details->max(fn($d) => $d->coreTemps->count()) ?: 1) }}">Actual Core Temp (°C)</th>
                            <th rowspan="2" class="align-middle">Bentuk</th>
                            <th rowspan="2" class="align-middle">Warna</th>
                            <th rowspan="2" class="align-middle">Aroma</th>
                            <th rowspan="2" class="align-middle">Rasa</th>
                            <th rowspan="2" class="align-middle">Tekstur</th>
                        </tr>
                        <tr>
                            @php $maxTemp = max(1, $batch->details->max(fn($d) => $d->coreTemps->count()) ?: 1); @endphp
                            @for ($i = 1; $i <= $maxTemp; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batch->details as $detail)
                            <tr>
                                <td>{{ $detail->production_code ?? '-' }}</td>
                                <td>{{ $detail->start_process ?? '-' }}</td>
                                <td>{{ $detail->end_process ?? '-' }}</td>
                                <td>{{ $detail->setup_time ?? '-' }}</td>
                                <td>{{ $detail->room_temp ?? '-' }}</td>
                                @for ($i = 0; $i < $maxTemp; $i++)
                                    <td>{{ $detail->coreTemps[$i]->temp_value ?? '-' }}</td>
                                @endfor
                                <td>{{ $detail->sensory_bentuk ?? '-' }}</td>
                                <td>{{ $detail->sensory_warna ?? '-' }}</td>
                                <td>{{ $detail->sensory_aroma ?? '-' }}</td>
                                <td>{{ $detail->sensory_rasa ?? '-' }}</td>
                                <td>{{ $detail->sensory_tekstur ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $maxTemp + 5 }}" class="text-center">Belum ada baris detail.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-muted">Belum ada batch.</p>
    @endforelse

    {{-- D. CATATAN & DOKUMENTASI --}}
    <h6 class="fw-bold">D. Catatan & Dokumentasi</h6>
    <p class="mb-1">{{ $report->notes ?? '-' }}</p>
    @if($report->curve_url)
        <p class="mb-3">Kurva pemasakan dapat dilihat pada
            <a href="{{ $report->curve_url }}" target="_blank">{{ $report->curve_url }}</a>
        </p>
    @endif
</div>