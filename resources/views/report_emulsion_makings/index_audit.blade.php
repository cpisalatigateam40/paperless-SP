@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <x-audit-banner />
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between">
                <h5 style="color: #552f93;">
                    <i class="fas fa-user-shield mr-1"></i>
                    Verifikasi Proses Pembuatan Emulsi
                </h5>

                <div class="d-flex gap-2" style="gap: .4rem;">
                    {{-- SEARCH --}}
                    <form method="GET" action="{{ route('report_emulsion_makings.audit') }}"
                        class="d-flex align-items-center" style="gap: .4rem;">

                        <input type="text" name="search" class="form-control" placeholder="Cari shift, pembuat..."
                            value="{{ request('search') }}">

                        <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                            @foreach([5, 10, 25] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                    {{ $n }} / halaman
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-outline-primary">Cari</button>

                        @if(request('search') || request('per_page'))
                            <a href="{{ route('report_emulsion_makings.audit') }}" class="btn btn-danger"
                                title="Reset Filter">Reset</a>
                        @endif
                    </form>

                    @hasanyrole('admin|superadmin|SPV QC')
                    <a href="{{ route('report_emulsion_makings.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                <th>Ketidaksesuaian</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $report)
                                @php
                                    $ketidaksesuaian = 0;
                                    if ($report->header) {
                                        $ketidaksesuaian += $report->header->details
                                            ->filter(fn($d) => $d->conformity === 'x')
                                            ->count();
                                        $ketidaksesuaian += $report->header->agings
                                            ->filter(
                                                fn($a) =>
                                                    $a->sensory_color === 'x'
                                                    || $a->sensory_texture === 'x'
                                                    || $a->emulsion_result === 'Tidak OK'
                                            )->count();
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
                                    <td>
                                        {{ $report->header->production_code ?? '-' }}
                                    </td>
                                    <td>{{ $ketidaksesuaian > 0 ? 'Ada' : '-' }}</td>
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
                                            ? route('report_emulsion_makings.edit', $report->uuid)
                                            : route('report_emulsion_makings.copy-to-audit', $report->uuid) }}"
                                                                    class="btn btn-sm btn-warning"
                                                                    title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                        @endif

                                        {{-- Hapus: hanya salinan audit --}}
                                        @if($report->is_audit)
                                            @can('delete report')
                                                <form action="{{ route('report_emulsion_makings.destroy', $report->uuid) }}"
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
                                                    <form action="{{ route('report_emulsion_makings.known', $report->id) }}" method="POST"
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
                                                    <form action="{{ route('report_emulsion_makings.approve', $report->id) }}" method="POST"
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
                                        <a href="{{ route('report_emulsion_makings.export-pdf', $report->uuid) }}"
                                            class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>

                                        {{-- Dropdown audit (gear) --}}
                                        <x-audit-dropdown :item="$report" route-prefix="report_emulsion_makings" />
                                    </td>
                                </tr>

                                {{-- DETAIL --}}
                                <tr class="collapse" id="detail-{{ $report->id }}">
                                    <td colspan="100%">
                                        <div class="table-responsive p-2">
                                            <table class="table table-sm table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 200px;" colspan="2">JENIS EMULSI</th>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $report->header->emulsion_type ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <th colspan="2">KODE PRODUKSI</th>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $report->header->production_code ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <th rowspan="2">BAHAN BAKU</th>
                                                        <th rowspan="2">Berat (kg)</th>
                                                    </tr>
                                                    <tr>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <th>Suhu (°C)</th>
                                                            <th>Kesesuaian Formula</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($report->header->details ?? [] as $detail)
                                                        <tr>
                                                            <td>
                                                                @if($detail->isRawMaterial())
                                                                    {{ $detail->rawMaterial->material_name ?? '-' }}
                                                                @elseif($detail->isPremix())
                                                                    {{ $detail->premix->name ?? '-' }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $detail->weight ?? '-' }}</td>
                                                            @foreach($report->header->agings ?? [] as $idx => $aging)
                                                                @if($detail->aging_index == $idx)
                                                                    <td>{{ $detail->temperature ?? '-' }}</td>
                                                                    <td>{{ $detail->conformity ?? '-' }}</td>
                                                                @else
                                                                    <td>-</td>
                                                                    <td>-</td>
                                                                @endif
                                                            @endforeach
                                                        </tr>
                                                    @endforeach

                                                    <tr>
                                                        <td colspan="2">Waktu Awal Proses</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->start_aging ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Waktu Akhir Proses</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->finish_aging ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Sensori Warna</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->sensory_color ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Sensori Texture</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->sensory_texture ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Suhu Emulsi After Proses</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->temp_after ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Hasil emulsi (sensory)</td>
                                                        @foreach($report->header->agings ?? [] as $aging)
                                                            <td colspan="2">{{ $aging->emulsion_result ?? '-' }}</td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>

                                            {{-- Tambah detail: hanya salinan audit --}}
                                            @if($report->is_audit)
                                                @can('create report')
                                                    <div class="d-flex justify-content-end mt-2">
                                                        <a href="{{ route('report_emulsion_makings.add-detail', $report->uuid) }}"
                                                            class="btn btn-secondary btn-sm">
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
        $(document).ready(function () {
            setTimeout(() => {
                $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
            }, 3000);
        });
    </script>
@endsection