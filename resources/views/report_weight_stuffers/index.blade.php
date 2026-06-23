@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Berat Stuffer</h4>
            
            <div class="d-flex gap-2" style="gap: .4rem;">

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('report_weight_stuffers.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari laporan..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('section'))
                        <a href="{{ route('report_weight_stuffers.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
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
                    action-route="report-weight-stuffers.bulk-known"
                    count-route="report-weight-stuffers.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="report-weight-stuffers.bulk-approve"
                    count-route="report-weight-stuffers.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                {{-- Tombol Export Excel --}}
                <x-export-excel-modal 
                    :route="route('report_weight_stuffers.export')" 
                    title="Verifikasi Berat Stuffer" />

                @can('create report')
                <a href="{{ route('report_weight_stuffers.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
                @endcan
            </div>
        </div>
        <div class="card-body table-responsive">
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
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Nama Produk</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>
                                {{ $report->details->pluck('product.product_name')->filter()->unique()->implode(', ') ?: '-' }}
                            </td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report_weight_stuffers.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                <!-- @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_weight_stuffers.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif -->

                                @can('delete report')
                                <form action="{{ route('report_weight_stuffers.destroy', $report->uuid) }}"
                                    method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
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
                                <form action="{{ route('report_weight_stuffers.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                        <i class="fas fa-eye"></i>
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
                                <form action="{{ route('report_weight_stuffers.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_weight_stuffers.export-pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7" class="p-0">
                                <div class="px-4 py-3" style="background:#f8f9fa">

                                    @php
                                        $details   = $report->details;
                                        $isNewData = $details->whereNotNull('machine')->isNotEmpty();
                                    @endphp

                                    @if ($isNewData)
                                    {{-- ======================================================
                                        TAMPILAN BARU — data punya kolom machine
                                    ====================================================== --}}
                                    @php $grouped = $details->groupBy('product_uuid'); @endphp

                                    @forelse ($grouped as $productUuid => $machineDetails)
                                        @php
                                            $firstDetail = $machineDetails->first();
                                            $productName = $firstDetail->product->product_name ?? '-';
                                            $gramase     = !empty($firstDetail->gramase)
                                                            ? $firstDetail->gramase
                                                            : ($firstDetail->product->nett_weight ?? '-');
                                            $prodCode    = $firstDetail->production_code ?? '-';
                                        @endphp

                                        <div class="mb-1 mt-2">
                                            <span class="fw-bold" style="font-size:14px">{{ $productName }}</span>
                                            <span class="text-muted mx-2">·</span>
                                            <span class="text-muted" style="font-size:13px">Gramase: <strong>{{ $gramase }} g</strong></span>
                                            <span class="text-muted mx-2">·</span>
                                            <span class="text-muted" style="font-size:13px">Kode: <strong>{{ $prodCode }}</strong></span>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered table-sm align-middle mb-0" style="font-size:13px; min-width:700px">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:200px">Parameter</th>
                                                        <th style="width:80px" class="text-center">Standar</th>
                                                        @foreach ($machineDetails as $d)
                                                            @php
                                                                $machineName = match($d->machine) {
                                                                    'townsend'  => 'Townsend',
                                                                    'hitech'    => 'Hitech',
                                                                    'vemag'     => 'Vemag',
                                                                    'vemag2'    => 'Vemag 2',
                                                                    'handtmann' => 'Handtmann',
                                                                    default     => 'Mesin',
                                                                };
                                                            @endphp
                                                            <th class="text-center">
                                                                <div>{{ $machineName }}</div>
                                                                <div class="text-muted fw-normal" style="font-size:11px">
                                                                    {{ \Carbon\Carbon::parse($d->time)->format('H:i') }} WIB
                                                                </div>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="fw-semibold">Diameter Casing (mm)</td>
                                                        <td class="text-center text-muted">-</td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->cases->first()?->actual_case_2 ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>

                                                    <tr class="table-secondary">
                                                        <td colspan="{{ $machineDetails->count() + 2 }}" class="fw-bold" style="font-size:12px">BERAT PER 3 PCS (gr)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Standar Berat</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center text-muted">{{ $d->weight_standard ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Hasil Aktual</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @forelse ($d->weights as $w)
                                                                    <span class="badge bg-light text-dark border">{{ $w->actual_weight ?? '-' }}</span>
                                                                @empty
                                                                    <span class="text-muted">-</span>
                                                                @endforelse
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Rata-rata</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            @php $stuffer = match($d->machine) { 'townsend' => $d->townsend, 'hitech' => $d->hitech, 'vemag' => $d->vemag, 'vemag2' => $d->vemag2, 'handtmann' => $d->handtmann, default => null }; @endphp
                                                            <td class="text-center fw-semibold">{{ $stuffer?->avg_weight ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @if ($d->weight_status)
                                                                    <span class="badge bg-{{ $d->weight_status === 'OK' ? 'success' : 'danger' }}">{{ $d->weight_status }}</span>
                                                                @else - @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Tindakan Koreksi</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->weight_corrective_action ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Keterangan</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->weight_notes ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>

                                                    <tr class="table-secondary">
                                                        <td colspan="{{ $machineDetails->count() + 2 }}" class="fw-bold" style="font-size:12px">PANJANG PER PCS (mm)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Standar Panjang</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center text-muted">{{ $d->long_standard ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Hasil Aktual</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @forelse ($d->weights as $w)
                                                                    <span class="badge bg-light text-dark border">{{ $w->actual_long ?? '-' }}</span>
                                                                @empty
                                                                    <span class="text-muted">-</span>
                                                                @endforelse
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Rata-rata</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            @php $stuffer = match($d->machine) { 'townsend' => $d->townsend, 'hitech' => $d->hitech, 'vemag' => $d->vemag, 'vemag2' => $d->vemag2, 'handtmann' => $d->handtmann, default => null }; @endphp
                                                            <td class="text-center fw-semibold">{{ $stuffer?->avg_long ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @if ($d->long_status)
                                                                    <span class="badge bg-{{ $d->long_status === 'OK' ? 'success' : 'danger' }}">{{ $d->long_status }}</span>
                                                                @else - @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Tindakan Koreksi</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->long_corrective_action ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Keterangan</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->long_notes ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>

                                                    <tr class="table-secondary">
                                                        <td colspan="{{ $machineDetails->count() + 2 }}" class="fw-bold" style="font-size:12px">BERAT FLA (gr)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Standar Berat Fla</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center text-muted">{{ $d->fla_standard ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Hasil Aktual</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @forelse ($d->weights as $w)
                                                                    <span class="badge bg-light text-dark border">{{ $w->actual_fla ?? '-' }}</span>
                                                                @empty
                                                                    <span class="text-muted">-</span>
                                                                @endforelse
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Rata-rata</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            @php $stuffer = match($d->machine) { 'townsend' => $d->townsend, 'hitech' => $d->hitech, 'vemag' => $d->vemag, 'vemag2' => $d->vemag2, 'handtmann' => $d->handtmann, default => null }; @endphp
                                                            <td class="text-center fw-semibold">{{ $stuffer?->avg_fla ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">
                                                                @if ($d->fla_status)
                                                                    <span class="badge bg-{{ $d->fla_status === 'OK' ? 'success' : 'danger' }}">{{ $d->fla_status }}</span>
                                                                @else - @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Tindakan Koreksi</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->fla_corrective_action ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td>Keterangan</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            <td class="text-center">{{ $d->fla_notes ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>

                                                    <tr class="table-light">
                                                        <td class="fw-semibold">Catatan</td><td></td>
                                                        @foreach ($machineDetails as $d)
                                                            @php $stuffer = match($d->machine) { 'townsend' => $d->townsend, 'hitech' => $d->hitech, 'vemag' => $d->vemag, 'vemag2' => $d->vemag2, 'handtmann' => $d->handtmann, default => null }; @endphp
                                                            <td class="text-center">{{ $stuffer?->notes ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        {{-- Dokumentasi --}}
                                        @php
                                            $allDocs = $machineDetails->flatMap(fn($d) => $d->documentations);
                                        @endphp
                                        @if($allDocs->isNotEmpty())
                                            <div class="mb-3">
                                                <div class="fw-semibold mb-2" style="font-size:13px">Dokumentasi</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($allDocs as $doc)
                                                        <a href="{{ Storage::url($doc->image) }}" target="_blank">
                                                            <img src="{{ Storage::url($doc->image) }}"
                                                                style="width:90px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;"
                                                                alt="Dokumentasi">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <p class="text-muted py-2 mb-0">Belum ada detail untuk laporan ini.</p>
                                    @endforelse

                                    @else
                                    {{-- ======================================================
                                        TAMPILAN LAMA — fallback untuk data tanpa kolom machine
                                    ====================================================== --}}
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm text-center align-middle mb-4">
                                            <tr>
                                                <th class="text-start">Nama Produk</th>
                                                @foreach ($details as $d)
                                                    <th>{{ $d->product->product_name ?? '-' }}</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th class="text-start">Gramase</th>
                                                @foreach ($details as $d)
                                                    <th>{{ !empty($d->gramase) ? $d->gramase : ($d->product->nett_weight ?? '-') }} g</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th class="text-start">Kode Produksi</th>
                                                @foreach ($details as $d)
                                                    <td>{{ $d->production_code }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th class="text-start">Waktu Proses</th>
                                                @foreach ($details as $d)
                                                    <td>{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th class="text-start">Mesin Stuffer</th>
                                                @foreach ($details as $d)
                                                    @php
                                                        $machineName = '-';
                                                        if ($d->townsend)      $machineName = 'Townsend';
                                                        elseif ($d->hitech)    $machineName = 'Hitech';
                                                        elseif ($d->vemag)     $machineName = 'Vemag';
                                                        elseif ($d->vemag2)    $machineName = 'Vemag 2';
                                                        elseif ($d->handtmann) $machineName = 'Handtmann';
                                                    @endphp
                                                    <td>{{ $machineName }}</td>
                                                @endforeach
                                            </tr>

                                            @php
                                            $labels = [
                                                'Kecepatan Stuffer (rpm)'                            => 'speed',
                                                'Ukuran Casing<br><small>(Aktual Diameter)</small>'  => 'casing',
                                                'Standar Berat (gr)'                                 => 'standard',
                                                'Berat Aktual (gr)'                                  => 'actual_weight',
                                                'Rata-rata Berat Aktual (gr)'                        => 'avg',
                                                'Status Berat'                                       => 'weight_status',
                                                'Tindakan Koreksi Berat'                             => 'weight_corrective_action',
                                                'Keterangan Berat'                                   => 'weight_notes',
                                                'Standar Panjang'                                    => 'standard_long',
                                                'Panjang Aktual'                                     => 'actual_long',
                                                'Rata-rata Panjang Aktual'                           => 'avg_long',
                                                'Status Panjang'                                     => 'long_status',
                                                'Tindakan Koreksi Panjang'                           => 'long_corrective_action',
                                                'Keterangan Panjang'                                 => 'long_notes',
                                                'Standar Berat Fla'                                  => 'standard_fla',
                                                'Berat Aktual Fla'                                   => 'actual_fla',
                                                'Rata-rata Berat Aktual Fla'                         => 'avg_fla',
                                                'Status Berat Fla'                                   => 'fla_status',
                                                'Tindakan Koreksi Berat Fla'                         => 'fla_corrective_action',
                                                'Keterangan Berat Fla'                               => 'fla_notes',
                                                'Catatan'                                            => 'notes',
                                            ];
                                            @endphp

                                            @foreach ($labels as $label => $key)
                                            <tr>
                                                <td class="text-start">{!! $label !!}</td>
                                                @foreach ($details as $d)
                                                    @php
                                                        $stuffer = $d->townsend ?? $d->hitech ?? $d->vemag ?? $d->vemag2 ?? $d->handtmann;
                                                        $case    = $d->cases->first();
                                                    @endphp
                                                    @switch($key)
                                                        @case('speed')         <td>{{ $stuffer?->stuffer_speed ?? '-' }}</td> @break
                                                        @case('casing')        <td>{{ $case?->actual_case_2 ?? '-' }}</td> @break
                                                        @case('standard')      <td>{{ $d->weight_standard ?? '-' }}</td> @break
                                                        @case('actual_weight') <td>{{ $d->weights->pluck('actual_weight')->filter()->implode(' / ') ?: '-' }}</td> @break
                                                        @case('avg')           <td>{{ $stuffer?->avg_weight ?? '-' }}</td> @break
                                                        @case('weight_status') <td>{{ $d->weight_status ?? '-' }}</td> @break
                                                        @case('weight_corrective_action') <td>{{ $d->weight_corrective_action ?? '-' }}</td> @break
                                                        @case('weight_notes')  <td>{{ $d->weight_notes ?? '-' }}</td> @break
                                                        @case('standard_long') <td>{{ $d->long_standard ?? '-' }}</td> @break
                                                        @case('actual_long')   <td>{{ $d->weights->pluck('actual_long')->filter()->implode(' / ') ?: '-' }}</td> @break
                                                        @case('avg_long')      <td>{{ $stuffer?->avg_long ?? '-' }}</td> @break
                                                        @case('long_status')   <td>{{ $d->long_status ?? '-' }}</td> @break
                                                        @case('long_corrective_action') <td>{{ $d->long_corrective_action ?? '-' }}</td> @break
                                                        @case('long_notes')    <td>{{ $d->long_notes ?? '-' }}</td> @break
                                                        @case('standard_fla')  <td>{{ $d->fla_standard ?? '-' }}</td> @break
                                                        @case('actual_fla')    <td>{{ $d->weights->pluck('actual_fla')->filter()->implode(' / ') ?: '-' }}</td> @break
                                                        @case('avg_fla')       <td>{{ $stuffer?->avg_fla ?? '-' }}</td> @break
                                                        @case('fla_status')    <td>{{ $d->fla_status ?? '-' }}</td> @break
                                                        @case('fla_corrective_action') <td>{{ $d->fla_corrective_action ?? '-' }}</td> @break
                                                        @case('fla_notes')     <td>{{ $d->fla_notes ?? '-' }}</td> @break
                                                        @case('notes')         <td>{{ $stuffer?->notes ?? '-' }}</td> @break
                                                    @endswitch
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                    @endif

                                    <!-- @can('create report')
                                        <div class="d-flex justify-content-end pb-2">
                                            <a href="{{ route('report_weight_stuffers.add-detail', $report->uuid) }}"
                                                class="btn btn-secondary btn-sm">
                                                + Tambah Detail
                                            </a>
                                        </div>
                                    @endcan -->

                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6">Belum ada data laporan.</td>
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
});
</script>
@endsection