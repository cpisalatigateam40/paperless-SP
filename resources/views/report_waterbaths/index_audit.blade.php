@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pasteurisasi Produk di Waterbath
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_waterbaths.audit') }}"
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
                        <a href="{{ route('report_waterbaths.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_waterbaths.index') }}" class="btn btn-outline-secondary">
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
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        @php
                            $user = auth()->user();
                            $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
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
                                            ? route('report_waterbaths.edit', $report->uuid)
                                            : route('report_waterbaths.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_waterbaths.destroy', $report->uuid) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus data audit ini?')">
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
                                        <form action="{{ route('report_waterbaths.known', $report->id) }}" method="POST"
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
                                        <form action="{{ route('report_waterbaths.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_waterbaths.export_pdf', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_waterbaths" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produk</th>
                                                <th>Gramase</th>
                                                <th>Batch</th>
                                                <th>Jumlah</th>
                                                <th>Satuan</th>
                                                <th>Pasteurisasi</th>
                                                <th>Cooling Shock</th>
                                                <th>Dripping</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $max = max(
                                                    $report->details->count(),
                                                    $report->pasteurisasi->count(),
                                                    $report->coolingShocks->count(),
                                                    $report->drippings->count()
                                                );
                                            @endphp

                                            @for($i = 0; $i < $max; $i++)
                                            @php
                                                $d = $report->details->get($i);
                                                $p = $report->pasteurisasi->get($i);
                                                $c = $report->coolingShocks->get($i);
                                                $dr = $report->drippings->get($i);
                                            @endphp
                                            <tr>
                                                {{-- Detail Produk --}}
                                                <td>{{ $d?->product?->product_name ?? '-' }}</td>
                                                <td>{{ !empty($d?->gramase)
                                                        ? $d->gramase
                                                        : ($d?->product?->nett_weight ?? '-') }} g</td>
                                                <td>{{ $d?->batch_code ?? '-' }}</td>
                                                <td>{{ $d?->amount ?? '-' }}</td>
                                                <td>{{ $d?->unit ?? '-' }}</td>

                                                {{-- Pasteurisasi --}}
                                                <td>
                                                    @if($p)
                                                    Suhu Awal Produk: {{ $p->initial_product_temp }}<br>
                                                    Suhu Awal Air: {{ $p->initial_water_temp }}<br>
                                                    Start Pasteurisasi: {{ $p->start_time_pasteur }}<br>
                                                    Stop Pasteurisasi: {{ $p->stop_time_pasteur }}<br>
                                                    Suhu air setelah produk dimasukkan panel: {{ $p->water_temp_after_input_panel }}<br>
                                                    Suhu air setelah produk dimasukkan aktual: {{ $p->water_temp_after_input_actual }}<br>
                                                    Suhu air setting: {{ $p->water_temp_setting }}<br>
                                                    Suhu air aktual: {{ $p->water_temp_actual }}<br>
                                                    Suhu akhir air: {{ $p->water_temp_final }}<br>
                                                    Suhu akhir produk: {{ $p->product_temp_final }}<br>
                                                    @endif
                                                </td>

                                                {{-- Cooling Shock --}}
                                                <td>
                                                    @if($c)
                                                    Suhu Awal Air: {{ $c->initial_water_temp }}<br>
                                                    Start Pasteurisasi: {{ $c->start_time_pasteur }}<br>
                                                    Stop Pasteurisasi: {{ $c->stop_time_pasteur }}<br>
                                                    Suhu air setting: {{ $c->water_temp_setting }}<br>
                                                    Suhu air aktual: {{ $c->water_temp_actual }}<br>
                                                    Suhu akhir air: {{ $c->water_temp_final }}<br>
                                                    Suhu akhir produk: {{ $c->product_temp_final }}<br>
                                                    @endif
                                                </td>

                                                {{-- Dripping --}}
                                                <td>
                                                    @if($dr)
                                                    Start Pasteurisasi: {{ $dr->start_time_pasteur }}<br>
                                                    Stop Pasteurisasi: {{ $dr->stop_time_pasteur }}<br>
                                                    Suhu Zona Panas: {{ $dr->hot_zone_temperature }}<br>
                                                    Suhu Zona Dingin: {{ $dr->cold_zone_temperature }}<br>
                                                    Suhu Akhir Produk: {{ $dr->product_temp_final }}
                                                    @endif
                                                </td>

                                                <td>{{ $d?->note ?? '-' }}</td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <div class="d-flex justify-content-end mt-2">
                                            <a href="{{ route('report_waterbaths.add_detail', $report->uuid) }}"
                                                class="btn btn-sm btn-secondary">
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
                            <td colspan="7" class="text-center">Belum ada data laporan</td>
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
</script>
@endsection