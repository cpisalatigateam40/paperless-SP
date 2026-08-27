@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Verifikasi Proses Pemasakan di Boiling Tank</h5>

        <div class="d-flex gap-2" style="gap: .4rem;">
            @hasanyrole('admin|superadmin')
            <form method="GET" action="{{ route('report_boiling_tanks.index') }}">
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
                class="btn btn-outline-secondary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#oldReportModal">

                <i class="bx bx-history"></i>
                Akses Data Lama

            </button>

            {{-- SEARCH --}}
            <form method="GET"
                action="{{ route('report_boiling_tanks.index') }}"
                class="d-flex align-items-center"
                style="gap: .4rem;">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari laporan..."
                    value="{{ request('search') }}"
                >

                <button type="submit"
                        class="btn btn-outline-primary">
                    Cari
                </button>

                @if(request('search'))
                    <a href="{{ route('report_boiling_tanks.index') }}"
                    class="btn btn-danger">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Buttons --}}
            <div class="d-flex gap-2">
                @role('Produksi')
                <button type="button" class="btn btn-warning btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkKnown">
                    <i class="fas fa-check-double"></i> Approve (Produksi)
                </button>
                @endrole

                @role('SPV QC')
                <button type="button" class="btn btn-success btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkApprove">
                    <i class="fas fa-check-circle"></i> Approve (QC)
                </button>
                @endrole
            </div>

            <x-export-pdf-modal
                :route="route('report_boiling_tanks.export_pdf_bulk')"
                title="Boiling tank"
                modal-id="modalExportBoilingTank"
                :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
            />

            {{-- Modals --}}
            @role('Produksi')
            <x-bulk-approval-modal
                prefix="known"
                title="Produksi"
                color="warning"
                icon="fa-check-double"
                action-route="report_boiling_tanks.bulk-known"
                count-route="report_boiling_tanks.bulk-known-count"
                label="Approve Semua"
            />
            @endrole

            @role('SPV QC') 
            <x-bulk-approval-modal
                prefix="approve"
                title="QC"
                color="success"
                icon="fa-check-circle"
                action-route="report_boiling_tanks.bulk-approve"
                count-route="report_boiling_tanks.bulk-approve-count"
                label="Approve Semua"
            />
            @endrole

            <x-export-excel-modal
            :route="route('report_boiling_tanks.export')"
            title="Verifikasi Proses Pemasakan di Boiling Tank" />

            @can('create report')
            <a href="{{ route('report_boiling_tanks.create') }}" class="btn btn-primary">Tambah Laporan</a>
            @endcan
        </div>

        
    </div>

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
                'production_code' => 'Kode Produksi',
                'report_date' => 'Tanggal Laporan',
                'submitted_at' => 'Tanggal Submit',
            ]"
            :with-date-filter="true"
        />
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Produk</th>
                        <th>Kode Produk</th>
                        <th>Jml Kode Produksi</th>
                        <th>Status</th>
                        <th>Dibuat oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $i => $report)
                        <tr>
                            <td>{{ $i + $reports->firstItem() }}</td>
                            <td>{{ optional($report->date)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $report->shift }}</td>
                            <td>{{ $report->product->product_name ?? '-' }}</td>
                            <td>{{ $report->product_code ?? '-' }}</td>
                            <td class="text-center">{{ $report->details_count ?? $report->details->count() }}</td>
                            <td class="text-center" style="color: white !important;">
                                @if($report->status === 'selesai')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->uuid }}" aria-expanded="false">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report_boiling_tanks.edit', $report->uuid) }}"
                                   class="btn btn-sm btn-warning">
                                    {{ $report->status === 'draft' ? 'Lanjutkan' : 'Edit' }}
                                </a>
                                @endcan

                                @can('delete report')
                                <form action="{{ route('report_boiling_tanks.destroy', $report->uuid) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan

                                {{-- KNOWN --}}
                                @can('known report')

                                    @if(!$report->known_by)

                                    <form action="{{ route('report_boiling_tanks.known', $report->id) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check-double"></i>
                                        </button>

                                    </form>

                                    @else

                                    <span class="badge bg-success">
                                        {{ $report->known_by }}
                                    </span>

                                    @endif

                                @endcan

                                {{-- APPROVE --}}
                                @can('approve report')

                                    @if(!$report->approved_by)

                                    <form action="{{ route('report_boiling_tanks.approve', $report->id) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button class="btn btn-sm btn-success">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>

                                    </form>

                                    @else

                                    <span class="badge bg-success">
                                        {{ $report->approved_by }}
                                    </span>

                                    @endif

                                @endcan

                                <a href="{{ route('report_boiling_tanks.export_pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $report->uuid }}">
                            <td colspan="9" class="bg-light p-3">
                                @include('report_boiling_tanks._detail_view', ['report' => $report])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $reports->links('pagination::bootstrap-5') }}
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

                    <a href="{{ route('report_baso_cookings.index') }}"
                        class="btn btn-outline-primary d-flex justify-content-between align-items-center mb-3">

                        <span>
                            <i class="bx bx-file me-2"></i>
                            Verifikasi Proses Pemasakan di Boiling Tank
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
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection