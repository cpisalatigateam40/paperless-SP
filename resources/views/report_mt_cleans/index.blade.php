@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Pemeriksaan Kebersihan Magnet Trap</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_mt_cleans.index') }}"
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
                <form method="GET" action="{{ route('report_mt_cleans.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search'))
                        <a href="{{ route('report_mt_cleans.index') }}"
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
                        :route="route('report_mt_cleans.export_pdf_bulk')"
                        title="MT Clean"
                        modal-id="modalExportPdfMtClean"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('report_mt_cleans.exportExcel')"
                        title="MT Clean" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report_mt_cleans.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('report_mt_cleans.audit') }}" class="btn btn-sm btn-audit">
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
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle">Aksi</th>
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

                            <td class="align-middle">

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
                                            <i class="fas fa-check-double"></i>
                                        </button>

                                    </form>

                                    @else

                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->known_by }}
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

                                    <span class="badge bg-success"
                                        style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                        <i class="fas fa-check"></i> {{ $report->approved_by }}
                                    </span>

                                    @endif

                                @endcan

                                {{-- PDF --}}
                                <a href="{{ route('report_mt_cleans.exportPdf', $report->uuid) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_mt_cleans" />
                                @endhasanyrole -->

                            </td>
                        </tr>

                        {{-- DETAIL --}}
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">

                                <table class="table table-sm table-bordered mb-0">

                                    <thead class="text-center">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Berat Tangkapan Logam (gr)</th>
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

                                            <td>
                                                {{ $detail->metal_weight ?? '-' }}
                                            </td>

                                            <td class="text-center">
                                                {{ $detail->time
                                                    ? \Illuminate\Support\Str::substr($detail->time,0,5)
                                                    : '-' }}
                                            </td>

                                            <td>{{ $detail->mt_1 ?? '-' }}</td>

                                            <td>{{ $detail->mt_2 ?? '-' }}</td>

                                            <td class="align-middle text-center">
                                                @if ($detail->photos->isNotEmpty())
                                                    @foreach ($detail->photos as $photo)
                                                        <a href="{{ Storage::url($photo->file_path) }}" target="_blank" class="d-inline-block me-1 mb-1">
                                                            <img src="{{ Storage::url($photo->file_path) }}"
                                                                class="img-thumbnail"
                                                                style="width:50px;height:50px;object-fit:cover;"
                                                                alt="Foto"
                                                                loading="lazy">
                                                        </a>
                                                    @endforeach
                                                @else
                                                    {{ $detail->finding_type ?? '-' }}
                                                @endif
                                            </td>

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

                <div class="mt-3">
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