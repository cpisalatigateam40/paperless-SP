
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />

    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3" style="color: #552f93;">
                <i class="fas fa-user-shield"></i>
                Verifikasi Produk Reject Metal Detector
            </h5>

            <div class="audit-toolbar d-flex flex-row flex-wrap align-items-center">
                <form method="GET" action="{{ route('report_md_rejects.audit') }}"
                    class="audit-search d-flex flex-row flex-wrap align-items-center">
                    <input type="text" name="search"
                        class="form-control form-control-sm audit-search-input"
                        placeholder="Cari shift, line..." value="{{ request('search') }}">

                    <select name="per_page" class="form-control form-control-sm audit-per-page"
                            onchange="this.form.submit()">
                        @foreach([10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_md_rejects.audit') }}"
                           class="btn btn-sm btn-outline-danger">Reset</a>
                    @endif
                </form>

                <div class="audit-bulk-wrap">
                    <x-audit-bulk-auto route-prefix="report_md_rejects" />
                </div>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_md_rejects.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Line</th>
                            <th>Area</th>
                            <th>Jumlah Produk</th>
                            <th>Total Reject</th>
                            <th>Dibuat Oleh</th>
                            <th style="min-width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                            @php
                                $user = auth()->user();
                                $canManage = $user->hasAnyRole(['admin', 'superadmin', 'SPV QC']);
                                $showAuto = $canManage && $report->hasAuditNormalizeRules() && $report->wouldNormalizeAudit();
                                $showEdit = ! $showAuto
                                    && ! $user->hasRole('auditor')
                                    && ($canManage || $report->created_at->gt(now()->subHours(2)));
                            @endphp
                            <tr>
                                <td>{{ $records->firstItem() + $loop->index }}</td>
                                <td>{{ $report->date->format('Y-m-d') }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->line }}</td>
                                <td>{{ optional($report->area)->name }}</td>
                                <td>{{ $report->details->count() ?: '-' }}</td>
                                <td>{{ $report->details->sum('reject_qty') ?: '-' }}</td>
                                <td>
                                    {{ $report->created_by }} <br>
                                    @if($report->is_audit)
                                        <span class="badge bg-info" style="color: white;">Audit</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex" style="gap: .2rem;">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($showAuto)
                                            <form action="{{ route('report_md_rejects.auto-audit', $report->uuid) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-audit-solid"
                                                    title="Edit Otomatis (jadikan OK)">
                                                    <i class="fas fa-magic"></i>
                                                </button>
                                            </form>
                                        @elseif($showEdit)
                                            <a href="{{ $report->is_audit
                                                    ? route('report_md_rejects.edit', $report->uuid)
                                                    : route('report_md_rejects.copy-to-audit', $report->uuid) }}"
                                                class="btn btn-sm btn-warning"
                                                title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('report_md_rejects.export-pdf', $report->uuid) }}"
                                            class="btn btn-secondary btn-sm" target="_blank" title="Cetak PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="detail-{{ $report->id }}">
                                <td colspan="100%">
                                    @include('report-md-rejects._detail', ['report' => $report])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    setTimeout(() => {
        $('#success-alert, #info-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection