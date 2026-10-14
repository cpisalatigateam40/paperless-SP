

@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Verifikasi Kondisi Ruang Penyimpanan Bahan Baku dan Bahan Penunjang</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('cleanliness.index') }}"
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
                <form method="GET" action="{{ route('cleanliness.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search') || request('section'))
                        <a href="{{ route('cleanliness.index') }}"
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
                        :route="route('storage-rm-cleanliness.export_pdf_bulk')"
                        title="Verifikasi Kebersihan Ruang Penyimpanan"
                        modal-id="modalExportPdfCleanliness"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('cleanliness.export')"
                        title="Verifikasi Kebersihan Ruang Penyimpanan" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('cleanliness.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('cleanliness.audit') }}" class="btn btn-sm btn-audit">
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
            action-route="storage-rm-cleanliness.bulk-known"
            count-route="storage-rm-cleanliness.bulk-known-count"
            label="Approve Semua"
        />
        @endrole

        @role('SPV QC')
        <x-bulk-approval-modal
            prefix="approve"
            title="QC"
            color="success"
            icon="fa-check-circle"
            action-route="storage-rm-cleanliness.bulk-approve"
            count-route="storage-rm-cleanliness.bulk-approve-count"
            label="Approve Semua"
        />
        @endrole

        <div class="card-body" style="padding-top: 1rem !important;">
            <div class="table-responsive">
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

                @php
                $roomNames = $reports->pluck('room_name')->unique()->filter()->values();
                @endphp

                <ul class="nav nav-tabs mb-3" id="roomTabs" role="tablist">
                    @foreach($roomNames as $index => $room)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ Str::slug($room) }}-tab"
                            data-bs-toggle="tab" data-bs-target="#{{ Str::slug($room) }}" type="button" role="tab">
                            {{ $room }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="roomTabsContent">
                    @foreach($roomNames as $index => $room)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ Str::slug($room) }}"
                        role="tabpanel" aria-labelledby="{{ Str::slug($room) }}-tab">

                        @include('cleanliness._report-table', [
                        'filteredReports' => $reports->where('room_name', $room)
                        ])
                    </div>
                    @endforeach
                </div>

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
});
</script>
@endsection