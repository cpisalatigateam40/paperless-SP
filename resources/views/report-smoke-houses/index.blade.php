@extends('layouts.app')

@section('title', 'Report Smoke House')

@section('content')

<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Report Smoke House
            </h5>

            

            <div class="d-flex" style="gap: .4rem;">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report-smoke-houses.index') }}">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area"
                            class="form-select form-control form-control"
                            onchange="this.form.submit()">
                        <option value="">Semua Area</option>

                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endhasanyrole

                <button type="button"
                    class="btn btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#oldReportModal">

                    <i class="bx bx-history"></i>
                    Akses Data Lama

                </button>
                {{-- Buttons --}}
                <div class="d-flex gap-2">
                    @role('Produksi')
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalBulkKnown">
                        <i class="fas fa-check-double"></i> Approve (Produksi)
                    </button>
                    @endrole

                    @role('SPV QC')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalBulkApprove">
                        <i class="fas fa-check-circle"></i> Approve (QC)
                    </button>
                    @endrole
                </div>

                <x-export-pdf-modal :route="route('report-smoke-houses.export-pdf-bulk')" title="Smoke House Reports"
                    modal-id="modalExportPdfSmokeHouse" :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']" />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal prefix="known" title="Produksi" color="warning" icon="fa-check-double"
                    action-route="report-smoke-houses.bulk-known" count-route="report-smoke-houses.bulk-known-count"
                    label="Approve Semua" />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal prefix="approve" title="QC" color="success" icon="fa-check-circle"
                    action-route="report-smoke-houses.bulk-approve" count-route="report-smoke-houses.bulk-approve-count"
                    label="Approve Semua" />
                @endrole

                <x-export-excel-modal :route="route('report-smoke-houses.export')"
                    title="Verifikasi Pemasakan Smoke House" />

                @can('create report')
                <a href="{{ route('report-smoke-houses.create') }}" class="btn btn-primary">

                    <i class="bx bx-plus"></i>
                    Tambah Report

                </a>
                @endcan
            </div>



        </div>

        <div class=" card-body table-responsive">

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

                        <th width="140" class="text-center">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($reports as $report)

                    <tr>
                        <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ $report->shift }}
                        </td>

                        <td>
                            {{ $report->area->name ?? '-' }}
                        </td>

                        <td>
                            {{ $report->details->count() }}
                        </td>

                        @php
                            $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;
                        @endphp

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

                        <td>
                            {{ $report->creator->name ?? '-' }}
                        </td>

                        <td class="text-center align-middle d-flex" style="gap: .4rem;">

                            <button class="btn btn-sm btn-info btn-toggle">
                                <i class="fas fa-eye"></i>
                            </button>

                            @can('edit report')
                            <a href="{{ route('report-smoke-houses.edit',$report->uuid) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan

                            @can('delete report')
                            <form action="{{ route('report-smoke-houses.destroy',$report->uuid) }}" method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button onclick="return confirm('Hapus report ini?')" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report-smoke-houses.known', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                    <i class="fas fa-check-double"></i>
                                </button>
                            </form>
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
                            <form action="{{ route('report-smoke-houses.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                    <i class="fas fa-thumbs-up"></i>
                                </button>
                            </form>
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

                            <a href="{{ route('report-smoke-houses.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                        </td>

                    </tr>

                    <tr class="detail-row d-none">

                        <td colspan="8" class="bg-light">

                            @php
                            $SHOWERING_PROCESS = 'Showering & Cooling Down';
                            @endphp

                            @foreach($report->details as $detail)

                            @php
                            $cookingSteps = $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS);
                            $showeringSteps = $detail->steps->where('process_name', '==', $SHOWERING_PROCESS);

                            $duration = ($detail->start_process && $detail->end_process)
                            ?
                            \Carbon\Carbon::parse($detail->start_process)->diffInMinutes(\Carbon\Carbon::parse($detail->end_process))
                            : null;
                            @endphp

                            <div class="card mb-4">
                                <div class="card-body">

                                    {{-- ===== A. INFORMASI PRODUK ===== --}}
                                    <h6 class="fw-bold">A. Informasi Produk</h6>
                                    <table class="table table-borderless table-sm mb-3" style="max-width: 500px;">
                                        <tr>
                                            <td width="180">Hari, Tanggal</td>
                                            <td width="20">:</td>
                                            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('l, d F Y') }}
                                            </td>
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
                                            <td>{{ $detail->production_code }}</td>
                                        </tr>
                                        <tr>
                                            <td>Gramasi</td>
                                            <td>:</td>
                                            <td>{{ $detail->gramase }} gr</td>
                                        </tr>
                                        <tr>
                                            <td>Smoke House</td>
                                            <td>:</td>
                                            <td>{{ $detail->machine_name }}</td>
                                        </tr>
                                    </table>

                                    {{-- ===== B. HASIL VERIFIKASI COOKING ===== --}}
                                    <h6 class="fw-bold">B. Hasil Verifikasi Cooking</h6>
                                    <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                                        <tr>
                                            <td width="180">Nomor Smoke House</td>
                                            <td width="20">:</td>
                                            <td>{{ $detail->smoke_house_no }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Trolley</td>
                                            <td>:</td>
                                            <td>{{ $detail->trolley_count }} trolly</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Stick/Trolley</td>
                                            <td>:</td>
                                            <td>{{ $detail->stick_count }} stick</td>
                                        </tr>
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
                                                <tr>
                                                    <td colspan="9" class="text-muted">Belum ada data</td>
                                                </tr>
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

                                    {{-- ===== COOKING ULANG ===== --}}
                                    @foreach($detail->reworks as $rework)
                                    @php
                                    $reworkDuration = ($rework->start_process && $rework->end_process)
                                    ?
                                    \Carbon\Carbon::parse($rework->start_process)->diffInMinutes(\Carbon\Carbon::parse($rework->end_process))
                                    : null;
                                    @endphp

                                    <hr>
                                    <h6 class="fw-bold text-warning">Cooking Ulang</h6>
                                    <table class="table table-borderless table-sm mb-2" style="max-width: 500px;">
                                        <tr>
                                            <td width="180">Nomor Smoke House</td>
                                            <td width="20">:</td>
                                            <td>{{ $rework->smoke_house_no }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Trolley</td>
                                            <td>:</td>
                                            <td>{{ $rework->trolley_count }} trolly</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Stick/Trolley</td>
                                            <td>:</td>
                                            <td>{{ $rework->stick_count }} stick</td>
                                        </tr>
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
                                                <tr>
                                                    <td colspan="9" class="text-muted">Belum ada data</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endforeach

                                    {{-- ===== C. HASIL VERIFIKASI SHOWERING & COOLING DOWN ===== --}}
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
                                                <tr>
                                                    <td colspan="9" class="text-muted">Belum ada data</td>
                                                </tr>
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

                            {{-- ===== D. CATATAN & DOKUMENTASI (level report) ===== --}}
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

                        <td colspan="7" class="text-center">

                            Tidak ada data.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

            <div class="mt-3">
                {{ $reports->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="oldReportModal" tabindex="-1" aria-labelledby="oldReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="oldReportModalLabel">
                    Akses Data Lama
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p class="text-muted mb-3">
                    Pilih jenis report yang ingin dibuka.
                </p>

                <div class="d-grid gap-3">

                    <a href="{{ route('report_maurer_cookings.index') }}"
                        class="btn btn-outline-primary d-flex justify-content-between align-items-center mb-3">

                        <span>
                            <i class="bx bx-file me-2"></i>
                            Maurer
                        </span>

                        <i class="bx bx-chevron-right"></i>

                    </a>

                    <a href="{{ route('report_fessman_cookings.index') }}"
                        class="btn btn-outline-success d-flex justify-content-between align-items-center">

                        <span>
                            <i class="bx bx-file me-2"></i>
                            Fessman
                        </span>

                        <i class="bx bx-chevron-right"></i>

                    </a>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@endsection


@section('script')

<script>
document.querySelectorAll('.btn-toggle').forEach(function(btn) {

    btn.addEventListener('click', function() {

        let row = this.closest('tr').nextElementSibling;

        row.classList.toggle('d-none');

        let icon = this.querySelector('i');

        if (row.classList.contains('d-none')) {

            icon.classList.remove('bx-chevron-up');

            icon.classList.add('bx-chevron-down');

        } else {

            icon.classList.remove('bx-chevron-down');

            icon.classList.add('bx-chevron-up');

        }

    });

});
</script>

<script>
function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>

@endsection