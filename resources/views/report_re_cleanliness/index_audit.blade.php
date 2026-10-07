@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-re-cleanliness.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report-re-cleanliness.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report-re-cleanliness" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report-re-cleanliness.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $issues > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .3rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info" onclick="toggleDetail('{{ $report->uuid }}')"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report-re-cleanliness.edit', $report->uuid)
                                            : route('report-re-cleanliness.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report-re-cleanliness.destroy', $report->uuid) }}"
                                        method="POST" onsubmit="return confirm('Yakin hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report-re-cleanliness.known', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                            title="Diketahui oleh">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->known_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                            title="Diketahui oleh">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                    @if(!$report->approved_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report-re-cleanliness.approve', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                            title="Disetujui oleh">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->approved_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                            title="Disetujui oleh">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report-re-cleanliness.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report-re-cleanliness" />
                                @endhasanyrole
                            </td>
                        </tr>

                        {{-- Baris detail (ruangan & equipment) --}}
                        <tr id="detail-{{ $report->uuid }}" style="display: none;">
                            <td colspan="7">
                                {{-- Detail Ruangan --}}
                                <h6 style="font-weight: bold;">Pemeriksaan Ruangan</h6>
                                <table class="table table-bordered table-sm mb-4 align-middle">
                                    <thead class="align-middle text-center">
                                        <tr>
                                            <th rowspan="2" class="align-middle">No</th>
                                            <th rowspan="2" class="align-middle">Area Produksi / Elemen</th>
                                            <th colspan="2" class="align-middle">Kondisi</th>
                                            <th rowspan="2" class="align-middle">Keterangan</th>
                                            <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                            <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                                        </tr>
                                        <tr>
                                            <th>Bersih</th>
                                            <th>Kotor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @foreach ($report->roomDetails->groupBy(fn($d) => $d->room->name ?? '-') as $roomName => $details)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $no++ }}</td>
                                            <td class="fw-bold" colspan="6" style="font-weight: bold;">
                                                {{ strtoupper($roomName) }}
                                            </td>
                                        </tr>

                                        @foreach ($details as $detail)
                                        <tr>
                                            <td></td>
                                            <td>{{ optional($detail->element)->element_name }}</td>
                                            <td class="text-center">
                                                @if ($detail->condition === 'clean') ✔ @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($detail->condition === 'dirty') ✔ @endif
                                            </td>
                                            <td>{{ $detail->notes }}</td>
                                            <td>{{ $detail->corrective_action }}</td>
                                            <td>{{ $detail->verification }}</td>
                                        </tr>

                                        @foreach ($detail->followups as $index => $followup)
                                        <tr class="table-secondary">
                                            <td></td>
                                            <td colspan="3">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                            <td>{{ $followup->notes }}</td>
                                            <td>{{ $followup->corrective_action }}</td>
                                            <td>{{ $followup->verification }}</td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>

                                {{-- Detail Equipment --}}
                                <h6 style="font-weight: bold;">Pemeriksaan Mesin & Peralatan</h6>
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="align-middle text-center">
                                        <tr>
                                            <th rowspan="2" class="align-middle">No</th>
                                            <th rowspan="2" class="align-middle">Peralatan / Part</th>
                                            <th colspan="2" class="align-middle">Kondisi</th>
                                            <th rowspan="2" class="align-middle">Keterangan</th>
                                            <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                            <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                                        </tr>
                                        <tr>
                                            <th>Bersih</th>
                                            <th>Kotor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @foreach ($report->equipmentDetails->groupBy(fn($d) => $d->equipment->name ?? '-') as $equipmentName => $details)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $no++ }}</td>
                                            <td class="fw-bold" colspan="6" style="font-weight: bold;">
                                                {{ strtoupper($equipmentName) }}
                                            </td>
                                        </tr>

                                        @foreach ($details as $detail)
                                        <tr>
                                            <td></td>
                                            <td>{{ optional($detail->part)->part_name }}</td>
                                            <td class="text-center">
                                                @if ($detail->condition === 'clean') ✔ @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($detail->condition === 'dirty') ✔ @endif
                                            </td>
                                            <td>{{ $detail->notes }}</td>
                                            <td>{{ $detail->corrective_action }}</td>
                                            <td>{{ $detail->verification }}</td>
                                        </tr>

                                        @foreach ($detail->followups as $index => $followup)
                                        <tr class="table-secondary">
                                            <td></td>
                                            <td colspan="3">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                            <td>{{ $followup->notes }}</td>
                                            <td>{{ $followup->corrective_action }}</td>
                                            <td>{{ $followup->verification }}</td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
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