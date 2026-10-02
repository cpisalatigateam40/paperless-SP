@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Pemasakan di Steam Kettle (Data Audit)
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_sauces.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari kode produksi, shift, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_sauces.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_sauces.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
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
                        @forelse($records as $r)
                        @php
                            $totalKetidaksesuaian = 0;
                            foreach ($r->details as $dt) {
                                if (
                                    $dt->color === 'Tidak OK' ||
                                    $dt->aroma === 'Tidak OK' ||
                                    $dt->taste === 'Tidak OK' ||
                                    $dt->texture === 'Tidak OK'
                                ) {
                                    $totalKetidaksesuaian++;
                                }
                                $totalKetidaksesuaian += $dt->rawMaterials
                                    ->filter(fn ($rm) => $rm->sensory === 'Tidak OK')
                                    ->count();
                            }

                            $user = auth()->user();
                            $canEdit = $user->hasRole(['admin', 'SPV QC']) || $r->created_at->gt(now()->subHours(2));
                        @endphp
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $r->date }}</td>
                            <td>{{ $r->shift }}</td>
                            <td>{{ $r->created_at->format('H:i') }}</td>
                            <td>{{ $r->area->name ?? '-' }}</td>
                            <td>{{ $r->production_code ?? '-' }}</td>
                            <td>{{ $totalKetidaksesuaian > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $r->created_by }}
                                @if($r->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .3rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $r->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $r->is_audit
                                            ? route('report_sauces.edit', $r->uuid)
                                            : route('report_sauces.copy-to-audit', $r->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $r->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($r->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_sauces.destroy', $r->uuid) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus data audit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$r->known_by)
                                        @if($r->is_audit)
                                        <form action="{{ route('report_sauces.known', $r->id) }}" method="POST"
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
                                            <i class="fas fa-check"></i> {{ $r->known_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($r->known_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $r->known_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                    @if(!$r->approved_by)
                                        @if($r->is_audit)
                                        <form action="{{ route('report_sauces.approve', $r->id) }}" method="POST"
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
                                            <i class="fas fa-check"></i> {{ $r->approved_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($r->approved_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $r->approved_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report_sauces.export_pdf', $r->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$r" route-prefix="report_sauces" />
                                @endhasanyrole
                            </td>
                        </tr>

                        {{-- Detail Collapse --}}
                        <tr class="collapse" id="detail-{{ $r->id }}">
                            <td colspan="9">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle text-center">
                                        <tr>
                                            <th class="text-start">Nama Produk</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ $r->product->product_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Formula</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ $r->formula->formula_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Gramase</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ !empty($r->gramase)
                                                    ? $r->gramase
                                                    : ($r->product->nett_weight ?? '-') }} g</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Kode Produksi</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ $r->production_code }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Waktu (Start - Stop)</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ $r->start_time }} - {{ $r->end_time }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Nomor Mesin</th>
                                            <td colspan="20" class="text-left">
                                                @foreach($r->details as $detail)
                                                    {{ $detail->no_mesin }}@if(!$loop->last), @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Catatan &amp; Dokumentasi</th>
                                            <td colspan="20" class="text-start" style="text-align: start !important;">
                                                {{ $r->documentation_notes ?? '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th rowspan="2">Durasi Proses</th>
                                            <th colspan="5">Bahan Baku</th>
                                            <th colspan="8">Parameter Pemasakan</th>
                                            <th colspan="4">Produk Organoleptik</th>
                                            <th rowspan="2">Status Produk</th>
                                            <th rowspan="2">Tindakan Perbaikan</th>
                                            <th rowspan="2">Catatan</th>
                                        </tr>
                                        <tr>
                                            <th>Jenis Bahan</th>
                                            <th>Jumlah (Kg)</th>
                                            <th>Status</th>
                                            <th>Tindakan Koreksi</th>
                                            <th>Keterangan</th>

                                            <th>Mixing Paddle On</th>
                                            <th>Mixing Paddle Off</th>
                                            <th>Brix (%)</th>
                                            <th>Salinity (%)</th>
                                            <th>Pressure (Bar)</th>
                                            <th>Target Temp (°C)</th>
                                            <th>Actual Temp (°C)</th>

                                            <th>Kenampakan</th>
                                            <th>Warna</th>
                                            <th>Aroma</th>
                                            <th>Rasa</th>
                                            <th>Tekstur</th>
                                        </tr>

                                        @foreach($r->details as $d)
                                        <tr>
                                            <td>{{ $d->process_step }}</td>

                                            <td colspan="5" class="p-0">
                                                <table class="table table-sm mb-0 table-borderless">
                                                    @foreach($d->rawMaterials as $rm)
                                                    <tr>
                                                        <td style="text-align: start !important;">
                                                            @if($rm->material_type === 'premix')
                                                                {{ $rm->premix->name ?? '-' }} (Premix)
                                                            @else
                                                                {{ $rm->rawMaterial->material_name ?? '-' }}
                                                            @endif
                                                        </td>
                                                        <td style="text-align: start !important;">{{ $rm->amount }}</td>
                                                        <td style="text-align: start !important;">{{ $rm->sensory }}</td>
                                                        <td style="text-align: start !important;">{{ $rm->corrective_action ?? '-' }}</td>
                                                        <td style="text-align: start !important;">{{ $rm->keterangan ?? '-' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </table>
                                            </td>

                                            <td>{{ $d->mixing_paddle_on ? '✔' : '-' }}</td>
                                            <td>{{ $d->mixing_paddle_off ? '✔' : '-' }}</td>
                                            <td>{{ $d->brix }}</td>
                                            <td>{{ $d->salinity }}</td>
                                            <td>{{ $d->pressure }}</td>
                                            <td>{{ $d->target_temperature }}</td>
                                            <td>{{ $d->actual_temperature }}</td>

                                            <td>{{ $d->appearance }}</td>
                                            <td>{{ $d->color }}</td>
                                            <td>{{ $d->aroma }}</td>
                                            <td>{{ $d->taste }}</td>
                                            <td>{{ $d->texture }}</td>

                                            <td>{{ $d->product_status }}</td>
                                            <td>{{ $d->corrective_action }}</td>
                                            <td>{{ $d->notes }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">Belum ada laporan</td>
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