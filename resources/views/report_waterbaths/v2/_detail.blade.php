@php
    $parameters = \App\Models\MasterWaterbathParameter::PARAMETERS;

    // class merah kalau di luar standar (true = ok, false = di luar, null = tidak dinilai)
    $bad = fn (?bool $ok) => $ok === false ? 'text-danger fw-bold bg-danger-subtle' : '';
    $val = fn ($v) => filled($v) ? $v : '-';
@endphp

<div class="p-3 bg-light">
    {{-- Informasi produk --}}
    <div class="row g-2 mb-3">
        <div class="col-md-6"><strong>Produk:</strong> {{ $report->product->product_name ?? '-' }}</div>
        <div class="col-md-6"><strong>Gramasi:</strong> {{ $val($report->gramase) }} gr</div>
        <div class="col-md-6"><strong>Kode Produk:</strong> {{ $val($report->batches->first()?->batch_code) }}</div>
    </div>

    @forelse($report->runs as $run)
    <div class="card border mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <strong>
                @if($run->run_number === 1)
                    Hasil Verifikasi
                @else
                    Pasteurisasi Ulang{{ $run->run_number > 2 ? ' #' . ($run->run_number - 1) : '' }}
                @endif
            </strong>
            <span class="small text-muted">Waterbath No. {{ $val($run->waterbath_number) }}</span>
        </div>

        <div class="card-body p-3">
            <div class="mb-2">
                <strong>Initial Temperature:</strong>
                <span class="{{ $bad($run->isInitialTempOk()) }} px-1 rounded">{{ $val($run->initial_temp) }} °C</span>
                <small class="text-muted">(Std: {{ $run->initial_temp_range }} °C)</small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white text-center align-middle mb-2">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">Parameter</th>
                            <th colspan="2">Temp (°C)</th>
                            <th colspan="2">Time (menit)</th>
                            <th colspan="2">CT Prod. (°C)</th>
                        </tr>
                        <tr>
                            <th>Set</th><th>Act</th>
                            <th>Set</th><th>Act</th>
                            <th>Std</th><th>Act</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parameters as $key => $label)
                        @php $p = $run->parameter($key); @endphp
                        <tr>
                            <td class="text-start">{{ $label }}</td>

                            @if($p)
                                {{-- Temp --}}
                                <td>
                                    @if($p->isDrying())
                                        Hot zone: {{ $p->set_temp_range }}<br>
                                        Cold zone: {{ $p->cold_zone_set_range }}
                                    @else
                                        {{ $p->set_temp_range }}
                                    @endif
                                </td>
                                <td>
                                    @if($p->isDrying())
                                        <div class="{{ $bad($p->isActTempOk()) }} rounded">Hot zone: {{ $val($p->act_temp) }}</div>
                                        <div class="{{ $bad($p->isColdZoneActOk()) }} rounded">Cold zone: {{ $val($p->cold_zone_act) }}</div>
                                    @else
                                        <span class="{{ $bad($p->isActTempOk()) }} px-1 rounded">{{ $val($p->act_temp) }}</span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td>{{ $val($p->set_time) }}</td>
                                <td><span class="{{ $bad($p->isActTimeOk()) }} px-1 rounded">{{ $val($p->act_time) }}</span></td>

                                {{-- CT Prod --}}
                                <td>{{ $p->std_ct_range }}</td>
                                <td><span class="{{ $bad($p->isActCtOk()) }} px-1 rounded">{{ $val($p->act_ct) }}</span></td>
                            @else
                                <td colspan="6" class="text-muted">-</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-1">
                <strong>Sensori:</strong>
                @if($run->sensor_ok === null)
                    -
                @elseif($run->sensor_ok)
                    <span class="badge bg-success"><i class="fas fa-check"></i> Ok</span>
                @else
                    <span class="badge bg-danger"><i class="fas fa-times"></i> Tidak Ok</span>
                @endif
            </div>
            <div><strong>Notes:</strong> {{ $val($run->notes) }}</div>
        </div>
    </div>
    @empty
        <div class="text-muted">Belum ada data verifikasi.</div>
    @endforelse

    <small class="text-muted"><i class="fas fa-circle text-danger" style="font-size:.5rem"></i> Merah = di luar standar master</small>
</div>