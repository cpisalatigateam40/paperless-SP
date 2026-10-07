@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Pemeriksaan Kebersihan Setelah Change-Over
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_changeover_cleanings.audit') }}"
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
                        <a href="{{ route('report_changeover_cleanings.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report_changeover_cleanings" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_changeover_cleanings.index') }}" class="btn btn-outline-secondary btn-sm">
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
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Area</th>
                            <th>Produk</th>
                            <th>Section</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        @php
                            $productNames = $report->details
                                ->pluck('product.product_name')
                                ->filter()
                                ->unique()
                                ->implode(', ');

                            $headerSections = $report->details
                                ->where('group', 'mesin_peralatan')
                                ->pluck('item.section.section_name')
                                ->filter()
                                ->unique()
                                ->implode(', ');

                            $user = auth()->user();
                            $canEdit = ! $user->hasRole('auditor')
                                && ($user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(8)));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td>{{ $report->shift ?? '-' }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $productNames ?: '-' }}</td>
                            <td>{{ $headerSections ?: '-' }}</td>
                            <td>{{ $report->created_by ?? '-' }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_changeover_cleanings.edit', $report->uuid)
                                            : route('report_changeover_cleanings.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_changeover_cleanings.destroy', $report->uuid) }}"
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
                                        <form action="{{ route('report_changeover_cleanings.known', $report->id) }}"
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
                                        <form action="{{ route('report_changeover_cleanings.approve', $report->id) }}"
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

                                {{-- Export PDF --}}
                                <a href="{{ route('report_changeover_cleanings.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_changeover_cleanings" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                @php
                                    $batchesData = [];

                                    foreach ($report->details as $d) {
                                        $batchKey = $d->product_uuid . '|' . $d->time;

                                        if (!isset($batchesData[$batchKey])) {
                                            $batchesData[$batchKey] = [
                                                'product_name'    => $d->product->product_name ?? '-',
                                                'time'            => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                                                'production_code' => $d->production_code ?? '-',
                                                'sisa_bahan'      => [],
                                                'mesin_peralatan' => [],
                                                'kondisi_ruangan' => [],
                                            ];
                                        }

                                        $batchesData[$batchKey][$d->group][] = [
                                            'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                                            'score'             => $d->score,
                                            'notes'             => $d->notes,
                                            'corrective_action' => $d->corrective_action,
                                        ];
                                    }

                                    foreach ($batchesData as $key => $data) {
                                        $batchSections = $report->details
                                            ->where('group', 'mesin_peralatan')
                                            ->filter(fn ($d) => ($d->product_uuid . '|' . $d->time) === $key)
                                            ->pluck('item.section.section_name')
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');

                                        $batchesData[$key]['section_names'] = $batchSections ?: '-';
                                    }

                                    $groupLabels = [
                                        'sisa_bahan'      => 'Sisa Bahan dan Kemasan',
                                        'mesin_peralatan' => 'Mesin dan Peralatan',
                                        'kondisi_ruangan' => 'Kondisi Ruangan',
                                    ];

                                    $criteriaPairs = $criteriaPairs ?? [[1,2],[3,4],[5,6],[7,8]];
                                @endphp

                                @forelse($batchesData as $batch)
                                    <div class="border rounded p-2 mb-3">
                                        <table class="table table-sm table-borderless mb-2" style="width: auto;">
                                            <tr>
                                                <td class="fw-bold" style="width:140px;">Produk</td>
                                                <td>: {{ $batch['product_name'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Kode Produksi</td>
                                                <td>: {{ $batch['production_code'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Jam</td>
                                                <td>: {{ $batch['time'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Section</td>
                                                <td>: {{ $batch['section_names'] }}</td>
                                            </tr>
                                        </table>

                                        @foreach($groupLabels as $groupKey => $groupLabel)
                                            <h6 class="fw-bold mt-2">{{ $groupLabel }}</h6>
                                            <table class="table table-sm table-bordered mb-2">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40px;">No</th>
                                                        <th style="width:280px;">Item</th>
                                                        @foreach($criteriaPairs as $pair)
                                                            <th class="text-center" style="width:60px;">{{ implode('/', $pair) }}</th>
                                                        @endforeach
                                                        <th style="width:400px;">Tindakan Koreksi</th>
                                                        <th style="width:400px;">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($batch[$groupKey] as $i => $row)
                                                    @php
                                                        $rowScores = array_map('strval', (array) ($row['score'] ?? []));
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="text-start">{{ $row['name'] }}</td>
                                                        @foreach($criteriaPairs as $pair)
                                                            @php
                                                                $matched = collect($pair)->first(fn($num) => in_array((string) $num, $rowScores));
                                                            @endphp
                                                            <td class="text-center">{{ $matched ?? '' }}</td>
                                                        @endforeach
                                                        <td class="text-start">{{ $row['corrective_action'] ?? '-' }}</td>
                                                        <td class="text-start">{{ $row['notes'] ?? '-' }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="{{ 4 + count($criteriaPairs) }}">
                                                            Belum ada data {{ strtolower($groupLabel) }}
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada detail</p>
                                @endforelse
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada laporan.</td>
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