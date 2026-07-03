@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Laporan Kebersihan Magnet Trap</h4>

            <div class="d-flex gap-2" style="gap: .4rem;">

                {{-- SEARCH --}}
                <form method="GET"
                    action="{{ route('report_mt_cleans.index') }}"
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
                        <a href="{{ route('report_mt_cleans.index') }}"
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
                    :route="route('report_mt_cleans.export_pdf_bulk')"
                    title="MT Clean"
                    modal-id="modalExportPdfMtClean"
                />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal
                    prefix="known"
                    title="Produksi"
                    color="warning"
                    icon="fa-check-double"
                    action-route="report-mt-cleans.bulk-known"
                    count-route="report-mt-cleans.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC') 
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="report-mt-cleans.bulk-approve"
                    count-route="report-mt-cleans.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                <x-export-excel-modal
                :route="route('report_mt_cleans.exportExcel')"
                title="MT Clean" />

                @can('create report')
                <a href="{{ route('report_mt_cleans.create') }}"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i>
                    Tambah Laporan
                </a>
                @endcan

            </div>
        </div>

        <div class="card-body">

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

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($reports as $i => $report)

                        <tr>
                            <td class="align-middle">
                                {{ $i + $reports->firstItem() }}
                            </td>

                            <td class="align-middle">
                                {{ $report->date ? $report->date->format('d-m-Y') : '-' }}
                            </td>

                            <td class="align-middle">
                                {{ $report->shift ?? '-' }}
                            </td>

                            <td class="align-middle">
                                {{ $report->area->name ?? '-' }}
                            </td>

                            <td class="align-middle">
                                {{ $report->created_by ?? '-' }}
                            </td>

                            <td class="align-middle text-center">

                                {{-- DETAIL --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                        data-target="#detail-{{ $report->id }}"
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @php
                                    $user = auth()->user();

                                    $canEdit =
                                        $user->hasRole(['admin', 'SPV QC'])
                                        || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                <a href="{{ route('report_mt_cleans.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif -->
                                @can('edit report')
                                <a href="{{ route('report_mt_cleans.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('delete report')
                                <form action="{{ route('report_mt_cleans.destroy', $report->uuid) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin hapus laporan ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>
                                @endcan

                                {{-- KNOWN --}}
                                @can('known report')

                                    @if(!$report->known_by)

                                    <form action="{{ route('report_mt_cleans.known', $report->id) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye"></i>
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

                                    <form action="{{ route('report_mt_cleans.approve', $report->id) }}"
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

                                {{-- PDF --}}
                                <a href="{{ route('report_mt_cleans.exportPdf', $report->uuid) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                            </td>
                        </tr>

                        {{-- DETAIL --}}
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">

                                <table class="table table-sm table-bordered mb-0">

                                    <thead class="text-center">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Waktu</th>
                                            <th>MT 1</th>
                                            <th>MT 2</th>
                                            <th>Jenis Temuan</th>
                                            <th>Kondisi</th>
                                            <th>Catatan</th>
                                            <th>Tindakan Koreksi</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @forelse($report->details as $detail)

                                        <tr>
                                            <td>
                                                {{ $detail->product->product_name ?? '-' }}
                                            </td>

                                            <td class="text-center">
                                                {{ $detail->time
                                                    ? \Illuminate\Support\Str::substr($detail->time,0,5)
                                                    : '-' }}
                                            </td>

                                            <td>{{ $detail->mt_1 ?? '-' }}</td>

                                            <td>{{ $detail->mt_2 ?? '-' }}</td>

                                            <td>{{ $detail->finding_type ?? '-' }}</td>

                                            <td>{{ $detail->condition ?? '-' }}</td>

                                            <td>{{ $detail->note ?? '-' }}</td>

                                            <td>{{ $detail->corrective_action ?? '-' }}</td>
                                        </tr>

                                        @empty

                                        <tr>
                                            <td colspan="8" class="text-center">
                                                Belum ada detail
                                            </td>
                                        </tr>

                                        @endforelse

                                    </tbody>

                                </table>

                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada laporan.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $reports->links('pagination::bootstrap-5') }}
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

    $('.toggle-detail').on('click', function() {

        const target = $(this.dataset.target);

        const isHidden = target.hasClass('d-none');

        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
        } else {
            target.addClass('d-none');
        }
    });

});
</script>
@endsection