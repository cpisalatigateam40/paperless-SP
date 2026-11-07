@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3" style="color: #552f93;">
                <i class="fas fa-user-shield "></i>
                Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan
            </h5>

            <div class="audit-toolbar d-flex flex-row flex-wrap align-items-center">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-re-cleanliness.audit') }}"
                    class="audit-search d-flex flex-row flex-wrap align-items-center ">

                    <input type="text" name="search"
                        class="form-control form-control-sm  audit-search-input"
                        placeholder="Cari pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-control form-control-sm  audit-per-page"
                        onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-primary ">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report-re-cleanliness.audit') }}"
                            class="btn btn-sm btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                <div class="audit-bulk-wrap ">
                    <x-audit-bulk-auto route-prefix="report-re-cleanliness" />
                </div>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report-re-cleanliness.index') }}"
                    class="btn btn-sm btn-outline-secondary">
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
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $issues = $report->roomDetails->where('verification', 'Tidak OK')->count()
                                + $report->equipmentDetails->where('verification', 'Tidak OK')->count();

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2)));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                            <td>
                                @forelse ($report->verification_times as $t)
                                    <span>{{ $t }}</span>
                                @empty
                                    {{ $report->created_at ? $report->created_at->format('H:i') : '-' }}
                                @endforelse
                            </td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $issues > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->created_by }} <br>
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
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

                                    $sessionTimes = $report->roomDetails->concat($report->equipmentDetails)
                                        ->map(fn ($d) => $d->verification_time ? substr($d->verification_time, 0, 5) : '')
                                        ->unique()->sort()->values();

                                    if ($sessionTimes->isEmpty()) {
                                        $sessionTimes = collect(['']); // data lama tanpa jam
                                    }
                                @endphp

                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info" onclick="toggleDetail('{{ $report->uuid }}')"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($showAuto)
                                    {{-- Edit Otomatis (ungu solid, ikon saja) --}}
                                    <form action="{{ route('report-re-cleanliness.auto-audit', $report->uuid) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </form>
                                @elseif($showEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report-re-cleanliness.edit', $report->uuid)
                                            : route('report-re-cleanliness.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Export PDF --}}
                                <a href="{{ route('report-re-cleanliness.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        {{-- Baris detail (ruangan & equipment) --}}
                        @include('report_re_cleanliness._detail', ['report' => $report, 'cols' => 7])
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada laporan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-4">
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

function toggleDetail(uuid) {
    const row = document.getElementById('detail-' + uuid);
    row.style.display = (row.style.display === 'none') ? '' : 'none';
}
</script>
@endsection