@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Pemeriksaan Kontaminasi Benda Asing
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-foreign-objects.audit') }}"
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
                        <a href="{{ route('report-foreign-objects.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report-foreign-objects.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>No.</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Waktu</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Section</th>
                            <th class="align-middle">Kode Produksi</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        @php
                            $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2)));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
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
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex align-items-center" style="gap: .2rem;">
                                @php
                                    $user = auth()->user();
                                    $canManage = $user->hasAnyRole(['admin', 'superadmin', 'SPV QC']);
                                    $hasRules = method_exists($report, 'hasAuditNormalizeRules') && $report->hasAuditNormalizeRules();

                                    // ungu: masih ada ketidaksesuaian yang bisa diubah otomatis
                                    $showAuto = $canManage && $hasRules && $report->wouldNormalizeAudit();

                                    // kuning: sudah tidak ada yang perlu diubah otomatis
                                    $showEdit = ! $showAuto
                                        && $canEdit
                                        && ! $user->hasRole('auditor');
                                @endphp

                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($showAuto)
                                    {{-- Edit Otomatis (ungu solid, ikon saja) --}}
                                    <form action="{{ route('report-foreign-objects.auto-audit', $report->uuid) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </form>
                                @elseif($showEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report-foreign-objects.edit', $report->uuid)
                                            : route('report-foreign-objects.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Export PDF --}}
                                <a href="{{ route('report-foreign-objects.export-pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="9">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Jam</th>
                                                <th class="align-middle">Produk</th>
                                                <th class="align-middle">Gramase</th>
                                                <th class="align-middle">Kode Produksi</th>
                                                <th class="align-middle">Jenis Kontaminan</th>
                                                <th class="align-middle">Bukti</th>
                                                <th class="align-middle">Tahapan Analisis</th>
                                                <th class="align-middle">Asal Kontaminan</th>
                                                <th class="align-middle">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->time }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ !empty($detail->gramase)
                                                        ? $detail->gramase
                                                        : ($detail->product->nett_weight ?? '-') }} g</td>
                                                <td>{{ $detail->production_code }}</td>
                                                <td>{{ $detail->contaminant_type }}</td>
                                                <td>
                                                    @if($detail->evidence)
                                                    <a href="{{ asset('storage/' . $detail->evidence) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . $detail->evidence) }}"
                                                            alt="Bukti" width="60">
                                                    </a>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->analysis_stage }}</td>
                                                <td>{{ $detail->contaminant_origin }}</td>
                                                <td>{{ $detail->notes }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Tambah detail: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('create report')
                                    <div class="mt-2 d-flex justify-content-end">
                                        <a href="{{ route('report-foreign-objects.add-detail', $report->uuid) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            Tambah Detail
                                        </a>
                                    </div>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data.</td>
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
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
    }, 3000);
});

function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection