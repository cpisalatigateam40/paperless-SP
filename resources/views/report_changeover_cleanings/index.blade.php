@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Pemeriksaan Kebersihan Setelah Change-Over</h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_changeover_cleanings.index') }}">
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

                {{-- SEARCH --}}
                <form method="GET"
                    action="{{ route('report_changeover_cleanings.index') }}"
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
                        <a href="{{ route('report_changeover_cleanings.index') }}"
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
                    :route="route('report-changeover-cleanings.export_pdf_bulk')"
                    title="Kebersihan Setelah Pergantian Produk"
                    modal-id="modalExportPdfChangeoverCleaning"
                    :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal
                    prefix="known"
                    title="Produksi"
                    color="warning"
                    icon="fa-check-double"
                    action-route="report-changeover-cleanings.bulk-known"
                    count-route="report-changeover-cleanings.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC') 
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="report-changeover-cleanings.bulk-approve"
                    count-route="report-changeover-cleanings.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                <x-export-excel-modal
                :route="route('report_changeover_cleanings.exportExcel')"
                title="Kebersihan Setelah Pergantian Produk" />

                @can('create report')
                <a href="{{ route('report_changeover_cleanings.create') }}" class="btn btn-primary btn-sm">
                    Tambah Laporan
                </a>
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
                    'report_date' => 'Tanggal Laporan',
                    'submitted_at' => 'Tanggal Submit',
                ]"
                :with-date-filter="true"
            />

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="">No</th>
                            <th class="">Tanggal</th>
                            <th class="">Shift</th>
                            <th class="">Area</th>
                            <th class="">Produk</th>
                            <th class="">Section</th>
                            <th class="">Dibuat Oleh</th>
                            <th class="">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $i => $report)
                        @php
                            $productNames = $report->details
                                ->pluck('product.product_name')
                                ->filter()
                                ->unique()
                                ->implode(', ');

                            $sectionNames = $report->details
                                ->where('group', 'mesin_peralatan')
                                ->pluck('item.section.section_name')
                                ->filter()
                                ->unique()
                                ->implode(', ');
                        @endphp
                        <tr>
                            <td class="">{{ $i + $reports->firstItem() }}</td>
                            <td class="">{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td class="">{{ $report->shift ?? '-' }}</td>
                            <td class="">{{ $report->area->name ?? '-' }}</td>
                            <td class="">{{ $productNames ?: '-' }}</td>
                            <td class="">{{ $sectionNames ?: '-' }}</td>
                            <td class="">{{ $report->created_by ?? '-' }}</td>
                            <td class=" ">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(8));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_changeover_cleanings.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report_changeover_cleanings.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan

                                {{-- KNOWN --}}
                                @can('known report')

                                    @if(!$report->known_by)

                                    <form action="{{ route('report_changeover_cleanings.known', $report->id) }}"
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

                                    <form action="{{ route('report_changeover_cleanings.approve', $report->id) }}"
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

                                <a href="{{ route('report_changeover_cleanings.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                @php
                                    $batchesData = [];

                                    foreach ($report->details as $d) {
                                        $batchKey = $d->product_uuid . '|' . $d->time;

                                        if (!isset($batchesData[$batchKey])) {
                                            $batchesData[$batchKey] = [
                                                'product_name'     => $d->product->product_name ?? '-',
                                                'time'             => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                                                'production_code'  => $d->production_code ?? '-',
                                                'sisa_bahan'       => [],
                                                'mesin_peralatan'  => [],
                                                'kondisi_ruangan'  => [],
                                            ];
                                        }

                                        $batchesData[$batchKey][$d->group][] = [
                                            'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                                            'score'             => $d->score,
                                            'notes'             => $d->notes,
                                            'corrective_action' => $d->corrective_action,
                                        ];
                                    }

                                    // Ambil nama section unik dari item-item di grup Mesin & Peralatan, per batch
                                    foreach ($batchesData as $key => $data) {
                                        $sectionNames = $report->details
                                            ->where('group', 'mesin_peralatan')
                                            ->filter(fn ($d) => ($d->product_uuid . '|' . $d->time) === $key)
                                            ->pluck('item.section.section_name')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');

                                        $batchesData[$key]['section_names'] = $sectionNames ?: '-';
                                    }

                                    $groupLabels = [
                                        'sisa_bahan'      => 'Sisa Bahan dan Kemasan',
                                        'mesin_peralatan' => 'Mesin dan Peralatan',
                                        'kondisi_ruangan' => 'Kondisi Ruangan',
                                    ];
                                @endphp

                                @forelse($batchesData as $batch)
                                    <div class="border rounded p-2 mb-3">
                                        <table class="table table-sm table-borderless mb-2" style="width: auto;">
                                            <tr>
                                                <td class="fw-bold" style="width:140px;">Produk</td>
                                                <td>: {{ $batch['product_name'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Kode Produksi</td>
                                                <td>: {{ $batch['production_code'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Jam</td>
                                                <td>: {{ $batch['time'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Section</td>
                                                <td>: {{ $batch['section_names'] }}</td>
                                            </tr>
                                        </table>

                                        @foreach($groupLabels as $groupKey => $groupLabel)
                                            <h6 class="fw-bold mt-2">{{ $groupLabel }}</h6>
                                            <table class="table table-sm table-bordered mb-2">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40px;">No</th>
                                                        <th style="width:360px;">Item</th>
                                                        <th style="width:80px;">Kriteria</th>
                                                        <th style="width:460px;">Tindakan Koreksi</th>
                                                        <th style="width:460px;">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($batch[$groupKey] as $i => $row)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="text-start">{{ $row['name'] }}</td>
                                                        <td>{{ $row['score'] ?? '-' }}</td>
                                                        <td class="text-start">{{ $row['corrective_action'] ?? '-' }}</td>
                                                        <td class="text-start">{{ $row['notes'] ?? '-' }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5">Belum ada data {{ strtolower($groupLabel) }}</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada detail</p>
                                @endforelse
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="">Belum ada laporan.</td>
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