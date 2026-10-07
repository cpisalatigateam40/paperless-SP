@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Form Pengambilan Sample
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_lab_samples.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, storage, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_lab_samples.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_lab_samples.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>Sample Storage</th>
                            <th>Kode Produksi</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $codes = $report->details->pluck('production_code')->filter()->implode(', ');
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
                            <td>{{ $report->storage }}</td>
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
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_lab_samples.edit', $report->uuid)
                                            : route('report_lab_samples.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit (route memakai id) --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_lab_samples.destroy', $report->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_lab_samples.known', $report->id) }}" method="POST"
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
                                        <form action="{{ route('report_lab_samples.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_lab_samples.export-pdf', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_lab_samples" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="9">
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Nama Produk</th>
                                                <th>Gramase</th>
                                                <th>Kode Produksi</th>
                                                <th>Best Before</th>
                                                <th>Jenis Sample</th>
                                                <th>Jumlah</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($report->details as $j => $detail)
                                            <tr>
                                                <td class="text-center">{{ $j + 1 }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ $detail->gramase }}</td>
                                                <td>{{ $detail->production_code }}</td>
                                                <td>{{ $detail->best_before }}</td>
                                                <td>{{ $detail->sample_type }}</td>
                                                <td>{{ $detail->quantity }} {{ $detail->unit }}</td>
                                                <td>{{ $detail->notes }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('report_lab_samples.details.create', $report->uuid) }}"
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