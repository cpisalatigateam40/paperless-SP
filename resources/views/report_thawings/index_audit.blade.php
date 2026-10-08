@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Thawing
            </h5>

            <div class="d-flex align-items-center" style="gap:.4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_thawings.audit') }}"
                    class="d-flex align-items-center" style="gap:.4rem;">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Cari shift, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control form-control-sm"
                        onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary btn-sm">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_thawings.audit') }}" class="btn btn-danger btn-sm">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report_thawings" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_thawings.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Kode Produksi</th>
                            <th>Dibuat Oleh</th>
                            <th width="400">Aksi</th>
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
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td class="text-center">{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
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
                                @endphp

                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->uuid }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($showAuto)
                                    {{-- Edit Otomatis (ungu solid, ikon saja), redirect ke halaman edit --}}
                                    <form action="{{ route('report_thawings.auto-audit', $report->uuid) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </form>
                                @elseif($showEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_thawings.edit', $report->uuid)
                                            : route('report_thawings.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Export PDF --}}
                                <a href="{{ route('report_thawings.export_pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        {{-- DETAIL ROW --}}
                        <tr id="detail-{{ $report->uuid }}" class="d-none">
                            <td colspan="8">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Waktu Thawing Awal</th>
                                                <th>Waktu Thawing Akhir</th>
                                                <th>Kondisi Awal Kemasan RM (utuh/sobek)</th>
                                                <th>Nama Bahan Baku</th>
                                                <th>Kode Produksi</th>
                                                <th>Jumlah</th>
                                                <th>Kondisi Ruang</th>
                                                <th>Waktu Pemeriksaan</th>
                                                <th>Suhu Ruang (&deg;C)</th>
                                                <th>Suhu Air Thawing(&deg;C)</th>
                                                <th>Suhu Produk (&deg;C)</th>
                                                <th>Kondisi Produk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->start_thawing_time ?? '-' }}</td>
                                                <td>{{ $detail->end_thawing_time ?? '-' }}</td>
                                                <td>{{ $detail->package_condition ? ucfirst($detail->package_condition) : '-' }}</td>
                                                <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
                                                <td>{{ $detail->production_code ?? '-' }}</td>
                                                <td>{{ $detail->qty ?? '-' }}</td>
                                                <td>{{ $detail->room_condition ? ucfirst($detail->room_condition) : '-' }}</td>
                                                <td>{{ $detail->inspection_time ?? '-' }}</td>
                                                <td>{{ $detail->room_temp ? $detail->room_temp.' °C' : '-' }}</td>
                                                <td>{{ $detail->water_temp ? $detail->water_temp.' °C' : '-' }}</td>
                                                <td>{{ $detail->product_temp ? $detail->product_temp.' °C' : '-' }}</td>
                                                <td>{{ $detail->product_condition ? ucfirst($detail->product_condition) : '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('report_thawings.details.create', $report->uuid) }}"
                                                class="btn btn-sm btn-secondary mt-2">
                                                + Tambah Detail
                                            </a>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada data laporan</td>
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
setTimeout(function() {
    $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
}, 3000);

$('.toggle-detail').click(function() {
    const target = $($(this).data('target'));
    $('tr[id^="detail-"]').not(target).addClass('d-none');
    target.toggleClass('d-none');
});

function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection