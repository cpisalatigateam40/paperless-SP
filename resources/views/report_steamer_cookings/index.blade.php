@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Laporan Verifikasi Pemasakan di Steamer</h4>

            <div class="d-flex align-items-center" style="gap: .4rem;">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_steamer_cookings.index') }}">
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

                <x-export-pdf-modal :route="route('report_steamer_cookings.export-pdf-bulk')" title="Steamer Reports"
                    modal-id="modalExportPdfSmokeHouse" :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']" />

                <x-export-excel-modal :route="route('report_steamer_cookings.export_excel')"
                    title="Verifikasi Pemasakan di Steamer" />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal prefix="known" title="Produksi" color="warning" icon="fa-check-double"
                    action-route="report_steamer_cookings.bulk-known" count-route="report_steamer_cookings.bulk-known-count"
                    label="Approve Semua" />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal prefix="approve" title="QC" color="success" icon="fa-check-circle"
                    action-route="report_steamer_cookings.bulk-approve" count-route="report_steamer_cookings.bulk-approve-count"
                    label="Approve Semua" />
                @endrole

                @can('create report')
                <a href="{{ route('report_steamer_cookings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
                @endcan

            </div>
            
        </div>
        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
                @if(request('date'))
                <div class="col-md-2">
                    <a href="{{ route('report_steamer_cookings.index') }}" class="btn btn-outline-danger w-100">Reset</a>
                </div>
                @endif
            </form> -->

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Produk</th>
                            <th>Area</th>
                            <th>Gramasi</th>
                            <th>Jml Batch</th>
                            <th>Kode Produksi</th>
                            <th>Diperiksa</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $reports->firstItem() + $loop->index }}</td>
                                <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->product->product_name ?? '-' }}</td>
                                <td>{{ $report->area->name ?? '-' }}</td>
                                <td>{{ $report->gramase }}</td>
                                <td class="text-center">{{ $report->batches_count }}</td>
                                @php
                                    $codes = $report->batches
                                        ->flatMap(fn($batch) => $batch->details)
                                        ->pluck('production_code')
                                        ->filter()
                                        ->implode(', ');
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
                                <td>{{ $report->created_by ?? '-' }}</td>
                                <td class="d-flex" style="gap: .4rem;">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @can('edit report')
                                    <a href="{{ route('report_steamer_cookings.edit', $report->uuid) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    @endcan

                                    @can('delete report')
                                    <form action="{{ route('report_steamer_cookings.destroy', $report->uuid) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin hapus laporan ini beserta seluruh batch & detailnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endcan

                                    {{-- Known --}}
                                    @can('known report')
                                    @if(!$report->known_by)
                                    <form action="{{ route('report_steamer_cookings.known', $report->uuid) }}" method="POST"
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
                                    <form action="{{ route('report_steamer_cookings.approve', $report->uuid) }}" method="POST"
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

                                    <a href="{{ route('report_steamer_cookings.export_pdf', $report->uuid) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="10" class="p-0 border-0">
                                    <div class="collapse" id="detail-{{ $report->uuid }}">
                                        @include('report_steamer_cookings._detail', ['report' => $report])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada laporan.</td>
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

                    <a href="{{ route('report_rtg_steamers.index') }}"
                        class="btn btn-outline-primary d-flex justify-content-between align-items-center mb-3">

                        <span>
                            <i class="bx bx-file me-2"></i>
                            Pemeriksaan Pemasakan Dengan Steamer
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

<script>
function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection