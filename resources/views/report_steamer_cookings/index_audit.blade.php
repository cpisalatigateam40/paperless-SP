@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3" style="color: #552f93;">
                <i class="fas fa-user-shield "></i>
                Verifikasi Proses Pemasakan di Steamer
            </h5>

            <div class="audit-toolbar d-flex flex-row flex-wrap align-items-center">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_steamer_cookings.audit') }}"
                    class="audit-search d-flex flex-row flex-wrap align-items-center ">

                    <input type="text" name="search"
                        class="form-control form-control-sm  audit-search-input"
                        placeholder="Cari shift, kode produksi, catatan..." value="{{ request('search') }}">

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
                        <a href="{{ route('report_steamer_cookings.audit') }}"
                            class="btn btn-sm btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                <div class="audit-bulk-wrap ">
                    <x-audit-bulk-auto route-prefix="report_steamer_cookings" />
                </div>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_steamer_cookings.index') }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if (session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
            @endif
            @if (session('error'))
                <div id="error-alert" class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Produk</th>
                            <th>Area</th>
                            <th>Gramasi</th>
                            <th>Jml Batch</th>
                            <th>Kode Produksi</th>
                            <th>Diperiksa</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            // standard tidak diisi oleh auditIndex, jadi diisi di sini (dipakai _detail)
                            $report->standard = \App\Models\SteamerStandard::where('product_uuid', $report->product_uuid)
                                ->where('area_uuid', $report->area_uuid)
                                ->first();

                            $codes = $report->batches
                                ->flatMap(fn($batch) => $batch->details)
                                ->pluck('production_code')
                                ->filter()
                                ->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->product->product_name ?? '-' }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->gramase }}</td>
                            <td class="text-center">{{ $report->batches->count() }}</td>
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
                            <td>{{ $report->created_by ?? '-' }} <br>
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex" style="gap: .4rem;">
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

                                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if($showAuto)
                                        {{-- Edit Otomatis (ungu solid, ikon saja), redirect ke halaman edit --}}
                                        <form action="{{ route('report_steamer_cookings.auto-audit', $report->uuid) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                                <i class="fas fa-magic"></i>
                                            </button>
                                        </form>
                                    @elseif($showEdit)
                                        <a href="{{ $report->is_audit
                                                ? route('report_steamer_cookings.edit', $report->uuid)
                                                : route('report_steamer_cookings.copy-to-audit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    {{-- Export PDF --}}
                                    <a href="{{ route('report_steamer_cookings.export_pdf', $report->uuid) }}"
                                        class="btn btn-sm btn-outline-secondary" target="_blank" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" class="p-0 border-0">
                                <div class="collapse" id="detail-{{ $report->uuid }}">
                                    @include('report_steamer_cookings._detail', ['report' => $report])
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada laporan.</td>
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

<script>
setTimeout(function () {
    $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
}, 3000);

function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection