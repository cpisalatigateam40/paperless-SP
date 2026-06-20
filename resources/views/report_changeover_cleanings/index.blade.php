@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Pemeriksaan Kebersihan Setelah Pergantian Produk</h4>

            <div class="d-flex gap-2" style="gap: .4rem;">
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
                    <i class="fas fa-plus"></i> Tambah Laporan
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
                            <td class="align-middle">{{ $i + $reports->firstItem() }}</td>
                            <td class="align-middle">{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td class="align-middle">{{ $report->shift ?? '-' }}</td>
                            <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                            <td class="align-middle">{{ $report->created_by ?? '-' }}</td>
                            <td class="align-middle text-center">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report_changeover_cleanings.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

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

                                    <form action="{{ route('report_changeover_cleanings.approve', $report->id) }}"
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

                                <a href="{{ route('report_changeover_cleanings.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                @php
                                    // Susun matriks: kolom = batch (produk+jam unik), baris = item
                                    $batches = [];
                                    $matrix  = [];

                                    foreach ($report->details as $d) {
                                        $batchKey = $d->product_uuid . '|' . $d->time;

                                        if (!isset($batches[$batchKey])) {
                                            $batches[$batchKey] = [
                                                'product_name' => $d->product->product_name ?? '-',
                                                'time'         => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                                            ];
                                        }

                                        $matrix[$d->item_uuid][$batchKey] = $d;
                                    }

                                    // Daftar item unik yang muncul di laporan ini
                                    $reportItems = $report->details
                                        ->groupBy('item_uuid')
                                        ->map(fn ($group) => $group->first()->item)
                                        ->filter()
                                        ->sortBy([['category', 'asc'], ['name', 'asc']])
                                        ->values();

                                    $colCount = 2 + (count($batches) * 2) + 2;
                                @endphp

                                <table class="table table-sm table-bordered mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">No</th>
                                            <th rowspan="2" class="align-middle">Item</th>
                                            @foreach($batches as $batch)
                                            <th colspan="2" class="align-middle">
                                                {{ $batch['product_name'] }}<br>
                                                <small>Jam: {{ $batch['time'] }}</small>
                                            </th>
                                            @endforeach
                                            <th rowspan="2" class="align-middle">Keterangan</th>
                                            <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                        </tr>
                                        <tr>
                                            @foreach($batches as $batch)
                                            <th class="align-middle">Hasil</th>
                                            <th class="align-middle">Penjelasan</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $groupedItems = $reportItems->groupBy('category');
                                            $no = 1;
                                        @endphp

                                        @forelse($groupedItems as $category => $itemsByCategory)

                                            <tr class="table-secondary">
                                                <td colspan="{{ $colCount }}" class="fw-bold text-start">
                                                    {{ $category }}
                                                </td>
                                            </tr>

                                            @foreach($itemsByCategory as $item)
                                            @php
                                                $rowKeterangan = [];
                                                $rowTindakan = [];
                                            @endphp

                                            <tr>
                                                <td class="align-middle">{{ $no++ }}</td>

                                                <td class="align-middle text-start">
                                                    <span class="ms-3">
                                                        {{ $item->name }}
                                                    </span>
                                                </td>

                                                @foreach($batches as $batchKey => $batch)
                                                    @php
                                                        $cell = $matrix[$item->uuid][$batchKey] ?? null;
                                                    @endphp

                                                    <td class="align-middle">
                                                        {{ $cell->result ?? '-' }}
                                                    </td>

                                                    <td class="align-middle">
                                                        {{ $cell->explanation ?? '-' }}
                                                    </td>

                                                    @php
                                                        if ($cell) {
                                                            if ($cell->notes) {
                                                                $rowKeterangan[] = $cell->notes;
                                                            }

                                                            if ($cell->corrective_action) {
                                                                $rowTindakan[] = $cell->corrective_action;
                                                            }
                                                        }
                                                    @endphp
                                                @endforeach

                                                <td class="align-middle text-start">
                                                    {{ $rowKeterangan ? implode('; ', $rowKeterangan) : '-' }}
                                                </td>

                                                <td class="align-middle text-start">
                                                    {{ $rowTindakan ? implode('; ', $rowTindakan) : '-' }}
                                                </td>
                                            </tr>

                                            @endforeach

                                        @empty
                                            <tr>
                                                <td colspan="{{ $colCount }}" class="text-center">
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
                            <td colspan="8" class="text-center">Belum ada laporan.</td>
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