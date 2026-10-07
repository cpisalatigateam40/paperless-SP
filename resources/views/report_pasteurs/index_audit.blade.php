@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pasteurisasi Produk di Retort Chamber
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_pasteurs.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, masalah, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_pasteurs.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_pasteurs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>Kode Produksi</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        @php
                            $codes = $report->details->pluck('product_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2)));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
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
                            <td class="d-flex" style="gap: .4rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_pasteurs.edit', $report->uuid)
                                            : route('report_pasteurs.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_pasteurs.destroy', $report->uuid) }}" method="POST"
                                        onsubmit="return confirm('Hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_pasteurs.known', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->known_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                    @if(!$report->approved_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_pasteurs.approve', $report->id) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->approved_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report_pasteurs.export_pdf', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_pasteurs" />
                                @endhasanyrole
                            </td>
                        </tr>

                        {{-- Detail Collapse --}}
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="8">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="text-center align-middle">
                                            <tr>
                                                <th style="width: 220px;">Keterangan</th>
                                                @foreach($report->details as $detail)
                                                <th>{{ $detail->product->product_name ?? '-' }} -
                                                    {{ !empty($detail->for_packaging_gr)
                                                        ? $detail->for_packaging_gr
                                                        : ($detail->product->nett_weight ?? '-') }} g</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Nomor Program</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->program_number ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Kode Produk</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->product_code ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Untuk Kemasan (gr)</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->for_packaging_gr ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jumlah Troly/Pack</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->trolley_count ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Suhu Produk</td>
                                                @foreach($report->details as $detail)
                                                <td>{{ $detail->product_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            @php
                                                $standardSteps = [
                                                    1 => 'Water Injection',
                                                    2 => 'Up Temperature',
                                                    3 => 'Pasteurisasi',
                                                    4 => 'Hot Water Recycling',
                                                    5 => 'Cooling Water Injection',
                                                    6 => 'Cooling Constant Temp.',
                                                    7 => 'Raw Cooling Water',
                                                ];
                                            @endphp

                                            @foreach($standardSteps as $order => $name)
                                            <tr class="bg-light">
                                                <td><strong>{{ $order }}. {{ $name }}</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Mulai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step?->standardStep?->start_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Selesai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step?->standardStep?->end_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Temp. Air (°C)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step?->standardStep?->water_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Pressure (MPa)</td>
                                                @foreach($report->details as $detail)
                                                @php $step = $detail->steps->firstWhere('step_order', $order); @endphp
                                                <td>{{ $step?->standardStep?->pressure ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach

                                            {{-- Step 8: Drainage --}}
                                            <tr class="bg-light">
                                                <td><strong>8. Drainage Pressure</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Mulai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                                                <td>{{ $drainage?->drainageStep?->start_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Jam Selesai (menit)</td>
                                                @foreach($report->details as $detail)
                                                @php $drainage = $detail->steps->firstWhere('step_order', 8); @endphp
                                                <td>{{ $drainage?->drainageStep?->end_time ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Step 9: Finish --}}
                                            <tr class="bg-light">
                                                <td><strong>9. Finish Produk</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Suhu Pusat Produk</td>
                                                @foreach($report->details as $detail)
                                                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                                                <td>{{ $finish?->finishStep?->product_core_temp ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Sortasi</td>
                                                @foreach($report->details as $detail)
                                                @php $finish = $detail->steps->firstWhere('step_order', 9); @endphp
                                                <td>{{ $finish?->finishStep?->sortation ?? '-' }}</td>
                                                @endforeach
                                            </tr>

                                            {{-- Paraf --}}
                                            <tr class="bg-light">
                                                <td><strong>Paraf</strong></td>
                                                @foreach($report->details as $detail)
                                                <td></td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>QC</td>
                                                @foreach($report->details as $detail)
                                                <td>
                                                    @if($detail->qc_paraf)
                                                    <img src="{{ asset('storage/' . $detail->qc_paraf) }}" alt="QC" width="60">
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Produksi</td>
                                                @foreach($report->details as $detail)
                                                <td>
                                                    @if($detail->production_paraf)
                                                    <img src="{{ asset('storage/' . $detail->production_paraf) }}"
                                                        alt="Produksi" width="60">
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="d-flex justify-content-end mt-3">
                                            <a href="{{ route('report_pasteurs.add_detail', $report->uuid) }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                Tambah Detail
                                            </a>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data laporan</td>
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