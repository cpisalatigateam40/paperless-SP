@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Kondisi Ruang Penyimpanan Bahan Baku dan Bahan Penunjang (Data Audit)
            </h6>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('cleanliness.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, ruangan, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('cleanliness.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('cleanliness.index') }}" class="btn btn-sm btn-outline-secondary">
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
                $roomNames = $records->pluck('room_name')->unique()->filter()->values();
                $itemLabelMap = ['Suhu ruang (℃) / RH (%)' => 'Suhu Ruang (°C)'];
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

                    @php $filteredReports = $records->where('room_name', $room); @endphp

                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
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
                                        if ($it->verification == 0) {
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
                                $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
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
                                    {{-- Toggle Detail --}}
                                    <button class="btn btn-sm btn-info toggle-detail"
                                        data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                    @if($canEdit)
                                        <a href="{{ $report->is_audit
                                                ? route('cleanliness.edit', $report->uuid)
                                                : route('cleanliness.copy-to-audit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    {{-- Hapus: hanya salinan audit (route memakai id) --}}
                                    @if($report->is_audit)
                                        @can('delete report')
                                        <form action="{{ route('cleanliness.destroy', $report->id) }}" method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Yakin ingin menghapus data audit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif

                                    {{-- Known --}}
                                    @can('known report')
                                        @if(!$report->known_by)
                                            @if($report->is_audit)
                                            <form action="{{ route('cleanliness.known', $report->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('Ketahui laporan ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @else
                                            <span class="badge bg-success"
                                                style="color:white; border-radius:1rem; padding-inline:.8rem; padding-block:.3rem;"
                                                title="Diketahui oleh">
                                                <i class="fas fa-check"></i> {{ $report->known_by }}
                                            </span>
                                        @endif
                                    @else
                                        @if($report->known_by)
                                            <span class="badge bg-success"
                                                style="color:white; border-radius:1rem; padding-inline:.8rem; padding-block:.3rem;"
                                                title="Diketahui oleh">
                                                <i class="fas fa-check"></i> {{ $report->known_by }}
                                            </span>
                                        @endif
                                    @endcan

                                    {{-- Approve --}}
                                    @can('approve report')
                                        @if(!$report->approved_by)
                                            @if($report->is_audit)
                                            <form action="{{ route('cleanliness.approve', $report->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('Setujui laporan ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-thumbs-up"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @else
                                            <span class="badge bg-success"
                                                style="color:white; border-radius:1rem; padding-inline:.8rem; padding-block:.3rem;"
                                                title="Disetujui oleh">
                                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                                            </span>
                                        @endif
                                    @else
                                        @if($report->approved_by)
                                            <span class="badge bg-success"
                                                style="color:white; border-radius:1rem; padding-inline:.8rem; padding-block:.3rem;"
                                                title="Disetujui oleh">
                                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                                            </span>
                                        @endif
                                    @endcan

                                    {{-- Export PDF --}}
                                    <a href="{{ route('cleanliness.export.pdf', $report->uuid) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    {{-- Dropdown audit (gear) --}}
                                    @hasanyrole('admin|superadmin|SPV QC')
                                    <x-audit-dropdown :item="$report" route-prefix="cleanliness" />
                                    @endhasanyrole
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
                                                    <th class="align-middle">Verifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($detail->items as $i => $item)

                                                {{-- Hide Item Index 3 (Item 4) ketika Chillroom --}}
                                                @if(($detail->room_name ?? $report->room_name) == '' && $i == 3)
                                                @continue
                                                @endif

                                                <tr>
                                                    <td class="align-middle">{{ $i + 1 }}</td>
                                                    <td class="align-middle">{{ $itemLabelMap[$item->item] ?? $item->item }}</td>
                                                    <td class="align-middle">{{ $item->condition }}</td>
                                                    <td class="align-middle">
                                                        @php $notes = json_decode($item->notes, true); @endphp
                                                        @if(is_array($notes))
                                                            {{ implode(', ', $notes) }}
                                                        @else
                                                            {{ $item->notes }}
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">{{ $item->corrective_action }}</td>
                                                    <td class="align-middle">{!! $item->verification ? '✔' : '✘' !!}</td>
                                                </tr>

                                                @foreach($item->followups as $fIndex => $followup)
                                                <tr class="table-secondary">
                                                    <td></td>
                                                    <td colspan="2" class="align-middle">
                                                        ↳ Koreksi Lanjutan #{{ $fIndex + 1 }}
                                                    </td>
                                                    <td class="align-middle">{{ $followup->notes }}</td>
                                                    <td class="align-middle">{{ $followup->corrective_action }}</td>
                                                    <td class="align-middle">{!! $followup->verification ? '✔' : '✘' !!}</td>
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
                                            <a href="{{ route('cleanliness.detail.create', $report->id) }}"
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
                                <td colspan="8" class="text-center">Tidak ada laporan pada ruangan ini.</td>
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