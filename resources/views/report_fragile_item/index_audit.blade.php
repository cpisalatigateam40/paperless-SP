@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 style="color: #552f93;">
                    <i class="fas fa-user-shield mr-1"></i>
                    Pemeriksaan Barang Mudah Pecah (Glass & Brittle Plastic)
                </h5>

                <div class="d-flex gap-2" style="gap: .4rem;">
                    {{-- SEARCH --}}
                    <form method="GET" action="{{ route('report-fragile-item.audit') }}"
                        class="d-flex align-items-center" style="gap: .4rem;">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari shift, pembuat..." value="{{ request('search') }}">

                        <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                            @foreach([5, 10, 25] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                    {{ $n }} / halaman
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-outline-primary">Cari</button>

                        @if(request('search') || request('per_page'))
                            <a href="{{ route('report-fragile-item.audit') }}" class="btn btn-danger">Reset</a>
                        @endif
                    </form>

                    <x-audit-bulk-auto route-prefix="report-fragile-item" />

                    @hasanyrole('admin|superadmin|SPV QC')
                    <a href="{{ route('report-fragile-item.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Data Operasional
                    </a>
                    @endhasanyrole
                </div>
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
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
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .2rem;">
                                @php
                                    $user = auth()->user();
                                    $canManage = $user->hasAnyRole(['admin', 'superadmin', 'SPV QC']);
                                    $hasRules = method_exists($report, 'hasAuditNormalizeRules') && $report->hasAuditNormalizeRules();

                                    // ungu: masih ada ketidaksesuaian yang bisa diubah otomatis
                                    $showAuto = $canManage && $hasRules && $report->wouldNormalizeAudit();

                                    // kuning: sudah tidak ada yang perlu diubah otomatis
                                    $showEdit = ! $showAuto
                                        && $user->can('edit report')
                                        && ! $user->hasRole('auditor');
                                @endphp

                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($showAuto)
                                    {{-- Edit Otomatis (ungu solid, ikon saja) --}}
                                    <form action="{{ route('report-fragile-item.auto-audit', $report->uuid) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </form>
                                @elseif($showEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report-fragile-item.edit-next', $report->uuid)
                                            : route('report-fragile-item.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-warning btn-sm"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endif

                                {{-- Export PDF --}}
                                <a href="{{ route('report-fragile-item.export', $report->uuid) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="7">
                                @php
                                    $groupedDetails = $report->details->groupBy(fn($d) => $d->item->section_name ?? '-');
                                    $no = 1;
                                @endphp

                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Pemilik (Area)</th>
                                            <th>Jumlah</th>
                                            <th>Waktu Awal</th>
                                            <th>Waktu Akhir</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupedDetails as $section => $items)
                                        <tr>
                                            <td colspan="7"><strong>{{ $section }}</strong></td>
                                        </tr>
                                        @foreach ($items as $detail)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $detail->item->item_name ?? '-' }}</td>
                                            <td>{{ $detail->item->owner ?? '-' }}</td>
                                            <td>{{ $detail->item->quantity ?? '-' }}</td>
                                            <td>{{ $detail->time_start == '1' ? '✓' : '' }}</td>
                                            <td>{{ $detail->time_end == '1' ? '✓' : '' }}</td>
                                            <td>{{ $detail->notes == '1' ? '✓' : '' }}</td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>

                                @if ($report->detailManuals->isNotEmpty())
                                <div class="mt-3">
                                    <strong>Input Manual Barang</strong>
                                    <table class="table table-sm table-bordered mb-0 mt-1">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Area</th>
                                                <th>Sub Area</th>
                                                <th>Nama Barang</th>
                                                <th>Jumlah</th>
                                                <th>Kondisi</th>
                                                <th>Nama Karyawan</th>
                                                <th>Temuan Ketidaksesuaian</th>
                                                <th>Tindakan Koreksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($report->detailManuals as $i => $manual)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $manual->section->section_name ?? '-' }}</td>
                                                <td>{{ $manual->sub_area ?? '-' }}</td>
                                                <td>{{ $manual->item_name }}</td>
                                                <td>{{ $manual->quantity }}</td>
                                                <td>{{ $manual->condition ?? '-' }}</td>
                                                <td>{{ $manual->employee_name ?? '-' }}</td>
                                                <td>{{ $manual->issue_notes ?? '-' }}</td>
                                                <td>{{ $manual->corrective_action ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
    }, 3000);

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('.toggle-detail').not(this).html('<i class="fas fa-eye"></i>');
        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
            $(this).html('<i class="fas fa-eye-slash"></i>');
        } else {
            target.addClass('d-none');
            $(this).html('<i class="fas fa-eye"></i>');
        }
    });
});
</script>
@endsection