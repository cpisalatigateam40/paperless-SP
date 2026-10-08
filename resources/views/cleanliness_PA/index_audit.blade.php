@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Kesesuaian Area Proses Produksi
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('process-area-cleanliness.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, section, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('process-area-cleanliness.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="process-area-cleanliness" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('process-area-cleanliness.index') }}" class="btn btn-sm btn-outline-secondary">
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

            @php
                $roomNames = $records->pluck('section_name')->unique()->filter()->values();
            @endphp

            @if($roomNames->isEmpty())
                <p class="text-center text-muted">Tidak ada data.</p>
            @endif

            <ul class="nav nav-tabs mb-3" role="tablist">
                @foreach($roomNames as $index => $room)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ Str::slug($room) }}-tab"
                        data-bs-toggle="tab" data-bs-target="#{{ Str::slug($room) }}" type="button" role="tab">
                        {{ $room }}
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach($roomNames as $index => $room)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ Str::slug($room) }}"
                    role="tabpanel">

                    @php $filteredReports = $records->where('section_name', $room); @endphp

                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Waktu</th>
                                <th>Area</th>
                                <th>Ketidaksesuaian</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredReports as $report)
                            @php
                                $count = 0;
                                foreach ($report->details as $dt) {
                                    foreach ($dt->items as $it) {
                                        if (strtolower($it->condition ?? '') === 'kotor' || $it->verification == 0) {
                                            $count++;
                                        }
                                        foreach ($it->followups as $fu) {
                                            if ($fu->verification == 0) {
                                                $count++;
                                            }
                                        }
                                    }
                                }

                                $user = auth()->user();
                                $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2)));
                            @endphp
                            <tr>
                                <td>{{ $records->firstItem() + $loop->index }}</td>
                                <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->created_at->format('H:i') }}</td>
                                <td>{{ $report->area->name ?? '-' }}</td>
                                <td>{{ $count > 0 ? 'Ada' : '-' }}</td>
                                <td>{{ $report->created_by }}
                                    @if($report->is_audit)
                                        <span class="badge bg-info" style="color: white;">Audit</span>
                                    @endif
                                </td>
                                <td class="d-flex" style="gap: .3rem;">
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

                                    {{-- Lihat Detail --}}
                                    <button class="btn btn-sm btn-info toggle-detail"
                                        data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if($showAuto)
                                        {{-- Edit Otomatis (ungu solid, ikon saja), redirect ke halaman edit --}}
                                        <form action="{{ route('process-area-cleanliness.auto-audit', $report->uuid) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Ubah semua ketidaksesuaian menjadi OK secara otomatis di data audit?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-audit-solid" title="Edit Otomatis (jadikan OK)">
                                                <i class="fas fa-magic"></i>
                                            </button>
                                        </form>
                                    @elseif($showEdit)
                                        <a href="{{ $report->is_audit
                                                ? route('process-area-cleanliness.edit', $report->uuid)
                                                : route('process-area-cleanliness.copy-to-audit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    {{-- Export PDF --}}
                                    <a href="{{ route('process-area-cleanliness.export.pdf', $report->uuid) }}"
                                        target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr class="collapse" id="detail-{{ $report->id }}">
                                <td colspan="8">
                                    <strong>Area:</strong> {{ $report->area->name ?? '-' }} <br>

                                    @if($report->approved_by)
                                    <div class="mb-2"><strong>Disetujui oleh:</strong> {{ $report->approved_by }}</div>
                                    @endif

                                    @foreach($report->details as $detail)
                                    <div class="mb-3 mt-3">
                                        <strong>Jam Inspeksi:</strong> {{ $detail->inspection_hour }}
                                        <table class="table table-sm mt-2">
                                            <thead>
                                                <tr>
                                                    <th class="align-middle">No</th>
                                                    <th class="align-middle">Item</th>
                                                    <th class="align-middle">Kondisi</th>
                                                    <th class="align-middle">Catatan</th>
                                                    <th class="align-middle">Tindakan Koreksi</th>
                                                    <th class="align-middle">Hasil Verifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($detail->items as $i => $item)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $item->item }}</td>
                                                    <td>
                                                        @if(Str::contains($item->item, 'Suhu ruang'))
                                                            Actual: {{ $item->temperature_actual ?? '-' }} ℃ <br>
                                                            Display: {{ $item->temperature_display ?? '-' }} ℃
                                                        @else
                                                            {{ $item->condition ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->notes }}</td>
                                                    <td>{{ $item->corrective_action }}</td>
                                                    <td>{!! $item->verification ? '✔' : '✘' !!}</td>
                                                </tr>

                                                @foreach($item->followups as $fIndex => $followup)
                                                <tr class="table-secondary">
                                                    <td></td>
                                                    <td colspan="2">↳ Koreksi Lanjutan #{{ $fIndex + 1 }}</td>
                                                    <td>{{ $followup->notes }}</td>
                                                    <td>{{ $followup->action }}</td>
                                                    <td>{!! $followup->verification ? '✔' : '✘' !!}</td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endforeach

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('process-area-cleanliness.detail.create', $report->id) }}"
                                                class="btn btn-sm btn-primary mt-3">
                                                + Tambah Detail Inspeksi
                                            </a>
                                        </div>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada laporan pada section ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
                @endforeach
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
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelector(this.getAttribute('data-target')).classList.toggle('show');
        });
    });
});

$(document).ready(function () {
    setTimeout(() => {
        $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection