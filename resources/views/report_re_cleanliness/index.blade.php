@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report-re-cleanliness.index') }}"
                    class="op-item op-form">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area" class="form-control form-control-sm" onchange="this.form.submit()">
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

                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-re-cleanliness.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search') || request('section'))
                        <a href="{{ route('report-re-cleanliness.index') }}"
                            class="btn btn-sm btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                {{-- Approve --}}
                @role('Produksi')
                <div class="op-item">
                    <button type="button" class="btn btn-warning btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkKnown">
                        <i class="fas fa-check-double"></i> Approve (Produksi)
                    </button>
                </div>
                @endrole

                @role('SPV QC')
                <div class="op-item">
                    <button type="button" class="btn btn-success btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkApprove">
                        <i class="fas fa-check-circle"></i> Approve (QC)
                    </button>
                </div>
                @endrole

                <div class="op-divider d-none d-lg-block"></div>

                {{-- Export --}}
                <div class="op-item">
                    <x-export-pdf-modal
                        :route="route('report-re-cleanliness.export_pdf_bulk')"
                        title="Laporan Kebersihan"
                        modal-id="modalExportPdfReportReCleanliness"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('report-re-cleanliness.export')"
                        title="Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report-re-cleanliness.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('report-re-cleanliness.audit') }}" class="btn btn-sm btn-audit">
                        <i class="fas fa-user-shield"></i> Audit
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Modals: di luar toolbar --}}
        @role('Produksi')
        <x-bulk-approval-modal
            prefix="known"
            title="Produksi"
            color="warning"
            icon="fa-check-double"
            action-route="report-re-cleanliness.bulk-known"
            count-route="report-re-cleanliness.bulk-known-count"
            label="Approve Semua"
        />
        @endrole

        @role('SPV QC')
        <x-bulk-approval-modal
            prefix="approve"
            title="QC"
            color="success"
            icon="fa-check-circle"
            action-route="report-re-cleanliness.bulk-approve"
            count-route="report-re-cleanliness.bulk-approve-count"
            label="Approve Semua"
        />
        @endrole
        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <x-report-sort
                :sort-options="[
                    'latest' => 'Terbaru',
                    'report_date' => 'Tanggal Laporan',
                    'submitted_at' => 'Tanggal Submit',
                ]"
                :with-date-filter="true"
            />

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                            <td>
                                @forelse ($report->verification_times as $t)
                                    <span>{{ $t }}</span>
                                @empty
                                    {{ $report->created_at ? $report->created_at->format('H:i') : '-' }}
                                @endforelse
                            </td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif

                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td >
                                <div class="d-flex" style="gap: .3rem;">

                                
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info" onclick="toggleDetail('{{ $report->uuid }}')"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report-re-cleanliness.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC'])
                                        || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if ($canEdit && ! $report->approved_by)
                                    <a href="{{ route('report-re-cleanliness.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report-re-cleanliness.destroy', $report->uuid) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-re-cleanliness.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                    title="Diketahui oleh">
                                    <i class="fas fa-check"></i> {{ $report->known_by }}
                                </span>
                                @endif
                                @else
                                @if($report->known_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                    title="Diketahui oleh">
                                    <i class="fas fa-check"></i> {{ $report->known_by }}
                                </span>
                                @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                @if(!$report->approved_by)
                                <form action="{{ route('report-re-cleanliness.approve', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                    title="Disetujui oleh">
                                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                                </span>
                                @endif
                                @else
                                @if($report->approved_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                    title="Disetujui oleh">
                                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                                </span>
                                @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report-re-cleanliness.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report-re-cleanliness" />
                                @endhasanyrole -->
                                </div>
                            </td>

                        </tr>
                        {{-- Baris detail (ruangan & equipment) --}}
                        @include('report_re_cleanliness._detail', ['report' => $report, 'cols' => 8])
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada laporan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-4">
                    {{ $reports->links('pagination::bootstrap-5') }}
                </div>
            </div>



            
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});

function toggleDetail(uuid) {
    const row = document.getElementById('detail-' + uuid);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>
@endsection