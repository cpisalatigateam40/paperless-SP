@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Pemeriksaan Kontaminasi Benda Asing</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report-foreign-objects.index') }}"
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
                <form method="GET" action="{{ route('report-foreign-objects.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search') || request('section'))
                        <a href="{{ route('report-foreign-objects.index') }}"
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
                        :route="route('report-foreign-objects.export_pdf_bulk')"
                        title="Benda Asing"
                        modal-id="modalExportPdfForeignObject"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('report-foreign-objects.export')"
                        title="Verifikasi Kontaminasi Benda Asing" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report-foreign-objects.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('report-foreign-objects.audit') }}" class="btn btn-sm btn-audit">
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
            action-route="report-foreign-objects.bulk-known"
            count-route="report-foreign-objects.bulk-known-count"
            label="Approve Semua"
        />
        @endrole

        @role('SPV QC')
        <x-bulk-approval-modal
            prefix="approve"
            title="QC"
            color="success"
            icon="fa-check-circle"
            action-route="report-foreign-objects.bulk-approve"
            count-route="report-foreign-objects.bulk-approve-count"
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
                    'production_code' => 'Kode Produksi',
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
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Waktu</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Section</th>
                            <th class="align-middle">Produk / RM</th>
                            <th class="align-middle">Kode Produksi</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
                            @php
                                $items = $report->details
                                    ->map(fn ($d) => $d->item_name)
                                    ->reject(fn ($name) => $name === '-')
                                    ->unique()
                                    ->implode(', ');
                                $itemsId = 'items-' . $report->uuid;
                            @endphp

                            <td>
                                @if($items)
                                    @if(strlen($items) > 50)
                                        <span id="{{ $itemsId }}-short">
                                            {{ \Illuminate\Support\Str::limit($items, 50) }}
                                            <a class="ms-1" href="#" onclick="toggleCodes('{{ $itemsId }}'); return false;">Show more</a>
                                        </span>

                                        <span id="{{ $itemsId }}-full" class="d-none">
                                            {{ $items }}
                                            <a class="ms-1" href="#" onclick="toggleCodes('{{ $itemsId }}'); return false;">Show less</a>
                                        </span>
                                    @else
                                        {{ $items }}
                                    @endif
                                @else
                                    -
                                @endif
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
                            <td>{{ $report->created_by }}</td>
                            <td >
                                <div class="d-flex align-items-center" style="gap: .2rem;">

                                
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report-foreign-objects.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report-foreign-objects.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report-foreign-objects.destroy', $report->uuid) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus seluruh laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
 
                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-foreign-objects.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report-foreign-objects.approve', $report->id) }}" method="POST"
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

                                {{-- Export PDF --}}
                                <a href="{{ route('report-foreign-objects.export-pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report-foreign-objects" />
                                @endhasanyrole -->
                                </div>
                            </td>

                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="10">
                                @forelse ($report->details as $detail)
                                    @include('report_foreign_objects._detail_card', [
                                        'detail' => $detail,
                                        'number' => $loop->iteration,
                                    ])
                                @empty
                                    <div class="text-center text-muted py-2">Tidak ada detail</div>
                                @endforelse

                                @if ($report->notes)
                                    <div class="small mb-2"><strong>Catatan:</strong> {{ $report->notes }}</div>
                                @endif

                                @can('create report')
                                    <div class="mt-2 d-flex justify-content-end">
                                        <a href="{{ route('report-foreign-objects.add-detail', $report->uuid) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            Tambah Detail
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
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
});
</script>

<script>
function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection