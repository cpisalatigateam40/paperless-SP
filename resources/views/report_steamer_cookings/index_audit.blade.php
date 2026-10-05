@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pemasakan di Steamer
            </h5>

            <div class="d-flex align-items-center" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_steamer_cookings.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control-sm form-control"
                        placeholder="Cari shift, kode produksi, catatan..." value="{{ request('search') }}">

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
                        <a href="{{ route('report_steamer_cookings.audit') }}" class="btn btn-danger btn-sm">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_steamer_cookings.index') }}" class="btn btn-outline-secondary btn-sm">
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
                            <td>{{ $report->created_by ?? '-' }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex" style="gap: .4rem;">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                    @can('edit report')
                                    <a href="{{ $report->is_audit
                                            ? route('report_steamer_cookings.edit', $report->uuid)
                                            : route('report_steamer_cookings.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan

                                    {{-- Hapus: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('delete report')
                                        <form action="{{ route('report_steamer_cookings.destroy', $report->uuid) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin hapus data audit ini beserta seluruh batch & detailnya?')">
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
                                            <form action="{{ route('report_steamer_cookings.known', $report->uuid) }}" method="POST"
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
                                            <form action="{{ route('report_steamer_cookings.approve', $report->uuid) }}" method="POST"
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
                                    <a href="{{ route('report_steamer_cookings.export_pdf', $report->uuid) }}"
                                        class="btn btn-sm btn-outline-secondary" target="_blank" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    {{-- Dropdown audit (gear) --}}
                                    @hasanyrole('admin|superadmin|SPV QC')
                                    <x-audit-dropdown :item="$report" route-prefix="report_steamer_cookings" />
                                    @endhasanyrole
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