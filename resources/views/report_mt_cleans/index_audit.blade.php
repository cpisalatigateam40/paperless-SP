@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Pemeriksaan Kebersihan Magnet Trap
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_mt_cleans.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_mt_cleans.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_mt_cleans.index') }}" class="btn btn-outline-secondary btn-sm">
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
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        <tr>
                            <td class="align-middle">{{ $records->firstItem() + $loop->index }}</td>
                            <td class="align-middle">{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td class="align-middle">{{ $report->shift ?? '-' }}</td>
                            <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                            <td class="align-middle">{{ $report->created_by ?? '-' }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                {{-- DETAIL --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @can('edit report')
                                <a href="{{ $report->is_audit
                                        ? route('report_mt_cleans.edit', $report->uuid)
                                        : route('report_mt_cleans.copy-to-audit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning"
                                    title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_mt_cleans.destroy', $report->uuid) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- KNOWN --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_mt_cleans.known', $report->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Diketahui">
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
                                @endcan

                                {{-- APPROVE --}}
                                @can('approve report')
                                    @if(!$report->approved_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report_mt_cleans.approve', $report->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Approve">
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
                                @endcan

                                {{-- PDF --}}
                                <a href="{{ route('report_mt_cleans.exportPdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_mt_cleans" />
                                @endhasanyrole
                            </td>
                        </tr>

                        {{-- DETAIL --}}
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Berat Tangkapan Logam (gr)</th>
                                            <th>Waktu</th>
                                            <th>MT 1</th>
                                            <th>MT 2</th>
                                            <th>Jenis Temuan</th>
                                            <th>Kondisi</th>
                                            <th>Catatan</th>
                                            <th>Tindakan Koreksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report->details as $detail)
                                        <tr>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->metal_weight ?? '-' }}</td>
                                            <td class="text-center">
                                                {{ $detail->time ? \Illuminate\Support\Str::substr($detail->time, 0, 5) : '-' }}
                                            </td>
                                            <td>{{ $detail->mt_1 ?? '-' }}</td>
                                            <td>{{ $detail->mt_2 ?? '-' }}</td>
                                            <td class="align-middle text-center">
                                                @if ($detail->photos->isNotEmpty())
                                                    @foreach ($detail->photos as $photo)
                                                        <a href="{{ Storage::url($photo->file_path) }}" target="_blank"
                                                            class="d-inline-block me-1 mb-1">
                                                            <img src="{{ Storage::url($photo->file_path) }}"
                                                                class="img-thumbnail"
                                                                style="width:50px;height:50px;object-fit:cover;"
                                                                alt="Foto" loading="lazy">
                                                        </a>
                                                    @endforeach
                                                @else
                                                    {{ $detail->finding_type ?? '-' }}
                                                @endif
                                            </td>
                                            <td>{{ $detail->condition ?? '-' }}</td>
                                            <td>{{ $detail->note ?? '-' }}</td>
                                            <td>{{ $detail->corrective_action ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Belum ada detail</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada laporan.</td>
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

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
        }
    });
});
</script>
@endsection