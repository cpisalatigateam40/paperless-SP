@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Pemeriksaan Produk Reject Metal Detector</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report-md-rejects.index') }}" class="op-item op-form">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <select name="area" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}" {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endhasanyrole

                <form method="GET" action="{{ route('report-md-rejects.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">
                    <input type="text" name="search" class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('report-md-rejects.index') }}" class="btn btn-sm btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                <div class="op-divider d-none d-lg-block"></div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report-md-rejects.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div id="error-alert" class="alert alert-danger">
                    <ul class="mb-0">
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
                            <th>Line</th>
                            <th>Area</th>
                            <th>Jumlah Produk</th>
                            <th>Total Reject</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date->format('Y-m-d') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->line }}</td>
                            <td>{{ optional($report->area)->name }}</td>
                            <td>{{ $report->details->count() ?: '-' }}</td>
                            <td>{{ $report->details->sum('reject_qty') ?: '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                <div class="d-flex" style="gap: .2rem;">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- @php
                                        $canEdit = auth()->user()->hasRole(['admin', 'SPV QC'])
                                            || $report->created_at->gt(now()->subHours(2));
                                    @endphp

                                    @if($canEdit)
                                        <a href="{{ route('report-md-rejects.edit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning" title="Edit Laporan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif -->

                                    
                                    <a href="{{ route('report-md-rejects.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    

                                    @can('delete report')
                                    <form action="{{ route('report-md-rejects.destroy', $report->uuid) }}"
                                        method="POST" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan

                                    {{-- Known --}}
                                    @can('known report')
                                        @if(!$report->known_by)
                                        <form action="{{ route('report-md-rejects.known', $report->uuid) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
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
                                        <form action="{{ route('report-md-rejects.approve', $report->uuid) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
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

                                    <a href="{{ route('report-md-rejects.export-pdf', $report->uuid) }}"
                                        class="btn btn-secondary btn-sm" target="_blank" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="100%">
                                @include('report-md-rejects._detail', ['report' => $report])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $reports->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection