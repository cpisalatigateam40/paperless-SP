@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pengemasan (Data Audit)
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_packaging_verifs.audit') }}"
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
                        <a href="{{ route('report_packaging_verifs.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_packaging_verifs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat oleh</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        @php
                            $totalNonConform = 0;
                            foreach ($report->details as $dt) {
                                $check = $dt->checklist;
                                if (!$check) continue;
                                foreach ([
                                    'sampling_result', 'verif_md',
                                    'sealing_condition_1','sealing_condition_2','sealing_condition_3',
                                    'sealing_condition_4','sealing_condition_5',
                                    'sealing_vacuum_1','sealing_vacuum_2','sealing_vacuum_3',
                                    'sealing_vacuum_4','sealing_vacuum_5',
                                ] as $field) {
                                    if (isset($check->$field) && $check->$field === 'Tidak OK') {
                                        $totalNonConform++;
                                    }
                                }
                            }

                            $user = auth()->user();
                            $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ optional($report->area)->name }}</td>
                            <td>{{ $totalNonConform > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_packaging_verifs.edit', $report->uuid)
                                            : route('report_packaging_verifs.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_packaging_verifs.destroy', $report->uuid) }}"
                                        method="POST" onsubmit="return confirm('Yakin hapus data audit ini?')">
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
                                        <form action="{{ route('report_packaging_verifs.known', $report->id) }}" method="POST"
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
                                        <form action="{{ route('report_packaging_verifs.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_packaging_verifs.export-pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_packaging_verifs" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="8">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center align-middle mb-4">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Jam</th>
                                                <th rowspan="2">Produk</th>
                                                <th rowspan="2">Gramase</th>
                                                <th rowspan="2">Kode Produksi</th>
                                                <th rowspan="2">Upload MD BPOM, QR Code, Kode Produksi, dan Expire Date</th>
                                                <th colspan="2">In cutting</th>
                                                <th colspan="2">Proses Pengemasan</th>
                                                <th colspan="2">Sampling Kemasan</th>
                                                <th colspan="2">Hasil Sealing</th>
                                                <th rowspan="2" class="text-nowrap">Isi Per-Pack</th>
                                                <th colspan="3">Panjang Produk Per Pcs</th>
                                                <th colspan="3">Berat Produk Per Pcs</th>
                                                <th colspan="3">Berat Produk Per Pack (gr)</th>
                                                <th rowspan="2">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <th>Manual</th>
                                                <th>Mesin</th>
                                                <th>Thermoformer</th>
                                                <th>Manual</th>
                                                <th>Jumlah Sampling</th>
                                                <th>Hasil Sampling</th>
                                                <th>Kondisi Seal</th>
                                                <th>Vacum</th>
                                                <th>Standar</th>
                                                <th>Aktual</th>
                                                <th>Rata-Rata</th>
                                                <th>Standar</th>
                                                <th>Aktual</th>
                                                <th>Rata-Rata</th>
                                                <th>Standar</th>
                                                <th>Aktual</th>
                                                <th>Rata-Rata</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($report->details as $d)
                                            @php
                                                $checklist = $d->checklist;
                                                $contentPerPack = is_array($checklist?->content_per_pack_json)
                                                    ? $checklist->content_per_pack_json
                                                    : json_decode($checklist?->content_per_pack_json ?? '[]', true);

                                                if (empty($contentPerPack)) {
                                                    $contentPerPack = collect(range(1, 5))
                                                        ->map(fn($n) => $checklist?->{'content_per_pack_' . $n})
                                                        ->filter(fn($v) => $v !== null && $v !== '')
                                                        ->values()
                                                        ->toArray();
                                                }
                                            @endphp

                                            @for($i = 1; $i <= 5; $i++)
                                            <tr>
                                                @if($i == 1)
                                                <td rowspan="5">{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}</td>
                                                <td rowspan="5">{{ $d->product->product_name ?? '-' }}</td>
                                                <td rowspan="5">{{ !empty($d->gramase)
                                                    ? $d->gramase
                                                    : ($d->product->nett_weight ?? '-') }} g</td>
                                                <td rowspan="5">{{ $d->production_code ?? '-' }}</td>
                                                <td rowspan="5">
                                                    @if(!empty($d->upload_md_multi))
                                                        @php
                                                            $files = array_filter(
                                                                json_decode($d->upload_md_multi, true) ?? [],
                                                                fn ($file) => !empty($file) && $file !== false
                                                            );
                                                        @endphp
                                                        @foreach($files as $file)
                                                            <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                                <img
                                                                    data-src="{{ asset('storage/' . $file) }}"
                                                                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                                                    alt="Bukti"
                                                                    width="60"
                                                                    class="lazy-img"
                                                                    style="margin:4px; cursor:pointer;">
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                </td>

                                                <td rowspan="5">{{ $checklist?->in_cutting_manual_1 ?? '-' }}</td>
                                                <td rowspan="5">{{ $checklist?->in_cutting_machine_1 ?? '-' }}</td>
                                                <td rowspan="5">{{ $checklist?->packaging_thermoformer_1 ?? '-' }}</td>
                                                <td rowspan="5">{{ $checklist?->packaging_manual_1 ?? '-' }}</td>
                                                <td rowspan="5">{{ $checklist?->sampling_amount ?? '-' }}
                                                    {{ $checklist?->unit ?? '-' }}</td>
                                                <td rowspan="5">{{ $checklist?->sampling_result ?? '-' }}</td>
                                                @endif

                                                <td>{{ $checklist?->{'sealing_condition_' . $i} ?? '-' }}</td>
                                                <td>{{ $checklist?->{'sealing_vacuum_' . $i} ?? '-' }}</td>
                                                <td>
                                                    @if(!empty($contentPerPack))
                                                        @foreach($contentPerPack as $idx => $val)
                                                            <div><small>Pack {{ $idx + 1 }}:</small> {{ $val ?? '-' }}</div>
                                                        @endforeach
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->standard_long_pcs ?? '-' }}</td>
                                                @endif
                                                <td>{{ $checklist?->{'actual_long_pcs_' . $i} ?? '-' }}</td>
                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->avg_long_pcs ?? '-' }}</td>
                                                @endif

                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->standard_weight_pcs ?? '-' }}</td>
                                                @endif
                                                <td>{{ $checklist?->{'actual_weight_pcs_' . $i} ?? '-' }}</td>
                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->avg_weight_pcs ?? '-' }}</td>
                                                @endif

                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->standard_weight ?? '-' }}</td>
                                                @endif
                                                <td>{{ $checklist?->{'actual_weight_' . $i} ?? '-' }}</td>
                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->avg_weight ?? '-' }}</td>
                                                @endif

                                                @if($i == 1)
                                                <td rowspan="5">{{ $checklist?->notes ?? '-' }}</td>
                                                @endif
                                            </tr>
                                            @endfor
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Tambah detail: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('create report')
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('report_packaging_verifs.add-detail', $report->uuid) }}"
                                            class="btn btn-secondary btn-sm">
                                            Tambah Detail
                                        </a>
                                    </div>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data.</td>
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

// Lazy load gambar hanya saat collapse row dibuka
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = document.querySelector(this.getAttribute('data-bs-target'));

        target?.addEventListener('shown.bs.collapse', function () {
            this.querySelectorAll('img.lazy-img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        }, { once: true });
    });
});
</script>
@endsection