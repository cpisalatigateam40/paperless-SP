@extends('layouts.app')

@section('title', 'Report Smoke House (Data Audit)')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pemasakan di Smoke House
            </h5>

            <div class="d-flex" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-smoke-houses.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, catatan..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report-smoke-houses.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report-smoke-houses.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body table-responsive" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
            @endif
            @if ($errors->any())
                <div id="error-alert" class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif


            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Total Batch</th>
                        <th>Kode Produksi</th>
                        <th>Dibuat</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $report)
                    @php
                        $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                        $collapseId = 'codes-' . $report->uuid;

                        $user = auth()->user();
                        $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(4));
                    @endphp
                    <tr>
                        <td>{{ $records->firstItem() + $loop->index }}</td>
                        <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->details->count() }}</td>
                        <td>
                            @if($codes)
                                @if(strlen($codes) > 50)
                                    <span id="{{ $collapseId }}-short">
                                        {{ \Illuminate\Support\Str::limit($codes, 50) }}
                                        <a class="ms-1" href="#" onclick="toggleCodes('{{ $collapseId }}'); return false;">Show more</a>
                                    </span>
                                    <span id="{{ $collapseId }}-full" class="d-none">
                                        {{ $codes }}
                                        <a class="ms-1" href="#" onclick="toggleCodes('{{ $collapseId }}'); return false;">Show less</a>
                                    </span>
                                @else
                                    {{ $codes }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $report->creator->name ?? '-' }}
                            @if($report->is_audit)
                                <span class="badge bg-info" style="color: white;">Audit</span>
                            @endif
                        </td>
                        <td class="text-center align-middle d-flex" style="gap: .4rem;">
                            <button class="btn btn-sm btn-info btn-toggle" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                            @if($canEdit)
                                <a href="{{ $report->is_audit
                                        ? route('report-smoke-houses.edit', $report->uuid)
                                        : route('report-smoke-houses.copy-to-audit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning"
                                    title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            {{-- Hapus: hanya salinan audit --}}
                            @if($report->is_audit)
                                @can('delete report')
                                <form action="{{ route('report-smoke-houses.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Hapus data audit ini?')" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            @endif

                            {{-- Known --}}
                            @can('known report')
                                @if(!$report->known_by)
                                    @if($report->is_audit)
                                    <form action="{{ route('report-smoke-houses.known', $report->id) }}" method="POST"
                                        style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->known_by }}
                                    </span>
                                @endif
                            @else
                                @if($report->known_by)
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->known_by }}
                                    </span>
                                @endif
                            @endcan

                            {{-- Approve --}}
                            @can('approve report')
                                @if(!$report->approved_by)
                                    @if($report->is_audit)
                                    <form action="{{ route('report-smoke-houses.approve', $report->id) }}" method="POST"
                                        style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->approved_by }}
                                    </span>
                                @endif
                            @else
                                @if($report->approved_by)
                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->approved_by }}
                                    </span>
                                @endif
                            @endcan

                            {{-- Export PDF --}}
                            <a href="{{ route('report-smoke-houses.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            {{-- Dropdown audit (gear) --}}
                            @hasanyrole('admin|superadmin|SPV QC')
                            <x-audit-dropdown :item="$report" route-prefix="report-smoke-houses" />
                            @endhasanyrole
                        </td>
                    </tr>

                    <tr class="detail-row d-none">
                        <td colspan="8" class="bg-light">
                            @php $SHOWERING_PROCESS = 'Showering & Cooling Down'; @endphp

                            @foreach($report->details as $detail)
                            @php
                                $cookingSteps = $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS);
                                $showeringSteps = $detail->steps->where('process_name', '==', $SHOWERING_PROCESS);

                                $duration = ($detail->start_process && $detail->end_process)
                                    ? \Carbon\Carbon::parse($detail->start_process)->diffInMinutes(\Carbon\Carbon::parse($detail->end_process))
                                    : null;
                            @endphp

                            <div class="card mb-4">
                                <div class="card-body">

                                    {{-- A. INFORMASI PRODUK --}}
                                    <h6 class="fw-bold">A. Informasi Produk</h6>
                                    <table class="table table-borderless table-sm mb-3" style="max-width: 500px;">
                                        <tr>
                                            <td width="180">Hari, Tanggal</td>
                                            <td width="20">:</td>
                                            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}</td>
                                        </tr>
                                        <tr><td>Shift</td><td>:</td><td>{{ $report->shift }}</td></tr>
                                        <tr><td>Nama Produk</td><td>:</td><td>{{ $detail->product->product_name ?? '-' }}</td></tr>
                                        <tr><td>Kode Produk</td><td>:</td><td>{{ $detail->production_code }}</td></tr>
                                        <tr><td>Gramasi</td><td>:</td><td>{{ $detail->gramase }} gr</td></tr>
                                        <tr><td>Smoke House</td><td>:</td><td>{{ $detail->machine_name }}</td></tr>
                                    </table>

                                    {{-- B. HASIL VERIFIKASI COOKING --}}
                                    <h6 class="fw-bold">B. Hasil Verifikasi Cooking</h6>
                                    <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                                        <tr><td width="180">Nomor Smoke House</td><td width="20">:</td><td>{{ $detail->smoke_house_no }}</td></tr>
                                        <tr><td>Jumlah Trolley</td><td>:</td><td>{{ $detail->trolley_count }} trolly</td></tr>
                                        <tr><td>Jumlah Stick/Trolley</td><td>:</td><td>{{ $detail->stick_count }} stick</td></tr>
                                        <tr>
                                            <td>Waktu Proses</td>
                                            <td>:</td>
                                            <td>
                                                {{ optional($detail->start_process)->format('H:i') }} -
                                                {{ optional($detail->end_process)->format('H:i') }}
                                                @if($duration !== null) ({{ $duration }} menit) @endif
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm text-center align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th>Setting Suhu (°C)</th>
                                                    <th>Aktual Suhu (°C)</th>
                                                    <th>Setup Time</th>
                                                    <th>Actual Time</th>
                                                    <th>Setting RH (%)</th>
                                                    <th>Aktual RH (%)</th>
                                                    <th>Setting Core</th>
                                                    <th>Aktual Core</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
                                                <tr><td colspan="9" class="text-muted">Belum ada data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    @if($detail->sensories)
                                    <div class="mb-3">
                                        <strong class="mt-3">Hasil Sensori:</strong>
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
                                    @foreach($detail->reworks as $rework)
                                    @php
                                        $reworkDuration = ($rework->start_process && $rework->end_process)
                                            ? \Carbon\Carbon::parse($rework->start_process)->diffInMinutes(\Carbon\Carbon::parse($rework->end_process))
                                            : null;
                                    @endphp

                                    <hr>
                                    <h6 class="fw-bold text-warning">Cooking Ulang</h6>
                                    <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                                        <tr><td width="180">Nomor Smoke House</td><td width="20">:</td><td>{{ $rework->smoke_house_no }}</td></tr>
                                        <tr><td>Jumlah Trolley</td><td>:</td><td>{{ $rework->trolley_count }} trolly</td></tr>
                                        <tr><td>Jumlah Stick/Trolley</td><td>:</td><td>{{ $rework->stick_count }} stick</td></tr>
                                        <tr>
                                            <td>Waktu Proses</td>
                                            <td>:</td>
                                            <td>
                                                {{ optional($rework->start_process)->format('H:i') }} -
                                                {{ optional($rework->end_process)->format('H:i') }}
                                                @if($reworkDuration !== null) ({{ $reworkDuration }} menit) @endif
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th>Setting Suhu (°C)</th>
                                                    <th>Aktual Suhu (°C)</th>
                                                    <th>Setup Time</th>
                                                    <th>Actual Time</th>
                                                    <th>Setting RH (%)</th>
                                                    <th>Aktual RH (%)</th>
                                                    <th>Setting Core</th>
                                                    <th>Aktual Core</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
                                                <tr><td colspan="9" class="text-muted">Belum ada data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endforeach

                                    {{-- C. SHOWERING & COOLING DOWN --}}
                                    <hr>
                                    <h6 class="fw-bold">C. Hasil Verifikasi Showering & Cooling Down</h6>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th>Setting Suhu (°C)</th>
                                                    <th>Aktual Suhu (°C)</th>
                                                    <th>Setup Time</th>
                                                    <th>Actual Time</th>
                                                    <th>Setting RH (%)</th>
                                                    <th>Aktual RH (%)</th>
                                                    <th>Setting Core</th>
                                                    <th>Aktual Core</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
                                                <tr><td colspan="9" class="text-muted">Belum ada data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="mb-0 mt-3">
                                        Proses Cooling Down Selesai :
                                        <strong>{{ optional($detail->cooling_finish)->format('H:i') }}</strong>
                                    </p>
                                </div>
                            </div>
                            @endforeach

                            {{-- D. CATATAN & DOKUMENTASI --}}
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="fw-bold">D. Catatan & Dokumentasi</h6>
                                    <p class="mb-0">{{ $report->notes ?: '-' }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
setTimeout(function () {
    $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
}, 3000);

document.querySelectorAll('.btn-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const row = this.closest('tr').nextElementSibling;
        row.classList.toggle('d-none');

        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye', row.classList.contains('d-none'));
        icon.classList.toggle('fa-eye-slash', !row.classList.contains('d-none'));
    });
});

function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection