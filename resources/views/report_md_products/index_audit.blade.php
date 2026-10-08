@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Kinerja Metal Detector Produk
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_md_products.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, pembuat, catatan..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_md_products.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report_md_products" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_md_products.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Metal Detector</th>
                            <th>Kode Produksi</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $codes = $report->details->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $totalNonConform = 0;
                            foreach ($report->details as $dt) {
                                if ($dt->positions->contains('status', false)) {
                                    $totalNonConform++;
                                }
                            }

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(8)));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>
                                @if ($report->metalDetector)
                                    {{ $report->metalDetector->merk }} - {{ $report->metalDetector->type_model }}
                                    <br><small class="text-muted">{{ $report->metalDetector->no_series }}</small>
                                @else
                                    -
                                @endif
                            </td>
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
                            <td>{{ $totalNonConform > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex" style="gap: .2rem;">
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
                                        {{-- Edit Otomatis (ungu solid, ikon saja), redirect ke halaman edit --}}
                                        <form action="{{ route('report_md_products.auto-audit', $report->uuid) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                                <i class="fas fa-magic"></i>
                                            </button>
                                        </form>
                                    @elseif($showEdit)
                                        <a href="{{ $report->is_audit
                                                ? route('report_md_products.edit', $report->uuid)
                                                : route('report_md_products.copy-to-audit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    {{-- Export PDF --}}
                                    <a href="{{ route('report_md_products.export-pdf', $report->uuid) }}"
                                        class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="10">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle">No</th>
                                                <th rowspan="2" class="align-middle">Waktu Verifikasi</th>
                                                <th rowspan="2" class="align-middle">Nama Produk</th>
                                                <th rowspan="2" class="align-middle">Gramase (gr)</th>
                                                <th rowspan="2" class="align-middle">Kode Produksi</th>
                                                <th rowspan="2" class="align-middle">No Program</th>
                                                <th colspan="3" class="align-middle">Fe 1.5 mm</th>
                                                <th colspan="3" class="align-middle">Non-Fe 2.0 mm</th>
                                                <th colspan="3" class="align-middle">SUS 2.5 mm</th>
                                                <th rowspan="2" class="align-middle">Status (OK/NG)</th>
                                                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                                <th rowspan="2" class="align-middle">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <th>D</th><th>T</th><th>B</th>
                                                <th>D</th><th>T</th><th>B</th>
                                                <th>D</th><th>T</th><th>B</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($report->details as $detail)
                                            @php
                                                $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
                                                $positions = ['d', 't', 'b'];
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ $detail->gramase ?? '-' }}</td>
                                                <td>{{ $detail->production_code ?? '-' }}</td>
                                                <td>{{ $detail->program_number ?? '-' }}</td>
                                                @foreach ($specimens as $specimen)
                                                    @foreach ($positions as $pos)
                                                    @php
                                                        $posDetail = $detail->positions
                                                            ->where('specimen', $specimen)
                                                            ->where('position', $pos)
                                                            ->first();
                                                    @endphp
                                                    <td>{{ $posDetail ? ($posDetail->status ? '✓' : '×') : '-' }}</td>
                                                    @endforeach
                                                @endforeach
                                                <td>
                                                    <span class="badge {{ $detail->status ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $detail->status ? 'OK' : 'NG' }}
                                                    </span>
                                                </td>
                                                <td>{{ $detail->corrective_action ?: '-' }}</td>
                                                <td>{{ $detail->verification ?: '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="18" class="text-center">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <p class="mt-2">Catatan: {{ $report->notes ?: '-' }}</p>

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="mt-2 d-flex justify-content-end">
                                            <a href="{{ route('report_md_products.add-detail', $report->uuid) }}"
                                                class="btn btn-sm btn-secondary">
                                                Tambah Detail Pemeriksaan
                                            </a>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada report</td>
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