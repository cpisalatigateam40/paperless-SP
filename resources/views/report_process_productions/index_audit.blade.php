@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-audit-banner />
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 style="color: #552f93;">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Proses Mixing, Chopping, dan Emulsifying
            </h5>

            <div class="d-flex gap-2" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET"
                    action="{{ route('report_process_productions.audit') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, pembuat, catatan..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report_process_productions.audit') }}"
                           class="btn btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                <x-audit-bulk-auto route-prefix="report_process_productions" />

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report_process_productions.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <th>Nama Produk</th>
                            <th>Kode Produksi</th>
                            <th>Formula</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $report)
                        @php
                            $detail = $report->detail->first();
                            $codes = $report->detail->pluck('production_code')->filter()->implode(', ');
                            $collapseId = 'codes-' . $report->uuid;

                            $total = 0;
                            foreach ($report->detail as $d) {
                                if (
                                    $d->sensory_homogenity === 'x' ||
                                    $d->sensory_stiffness === 'x' ||
                                    $d->sensory_aroma === 'x'
                                ) {
                                    $total++;
                                }
                                if ($d->sensoric) {
                                    if (
                                        $d->sensoric->homogeneous === 'Tidak OK' ||
                                        $d->sensoric->stiffness === 'Tidak OK' ||
                                        $d->sensoric->aroma === 'Tidak OK' ||
                                        $d->sensoric->foreign_object === 'Terdeteksi'
                                    ) {
                                        $total++;
                                    }
                                }
                                if ($d->items) {
                                    $total += $d->items->where('sensory', 'Tidak OK')->count();
                                }
                            }

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
                            <td>{{ $detail?->product?->product_name ?? '-' }}</td>
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
                            <td>{{ $detail->formula->formula_name ?? '-' }}</td>
                            <td>{{ $total > 0 ? 'Ada' : '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @if($canEdit)
                                    <a href="{{ $report->is_audit
                                            ? route('report_process_productions.edit', $report->uuid)
                                            : route('report_process_productions.copy-to-audit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report_process_productions.destroy', $report->uuid) }}"
                                        method="POST" onsubmit="return confirm('Hapus data audit ini?')">
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
                                        <form action="{{ route('report_process_productions.known', $report->id) }}"
                                            method="POST" style="display:inline-block;"
                                            onsubmit="return confirm('Ketahui laporan ini?')">
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
                                        <form action="{{ route('report_process_productions.approve', $report->id) }}"
                                            method="POST" style="display:inline-block;"
                                            onsubmit="return confirm('Setujui laporan ini?')">
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
                                <a href="{{ route('report_process_productions.export', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_process_productions" />
                                @endhasanyrole
                            </td>
                        </tr>

                        {{-- DETAIL COLLAPSIBLE --}}
                        <tr class="collapse" id="detail-{{ $report->uuid }}">
                            <td colspan="100%">
                                <div class="mt-3">
                                    @foreach ($report->detail as $detail)
                                    <table class="table table-bordered table-sm mb-2">
                                        <tr>
                                            <th colspan="2">NAMA PRODUK</th>
                                            <td colspan="5">{{ $detail->product->product_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">GRAMASE</th>
                                            <td colspan="5">{{ number_format($detail->gramase, 0) }} g</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">KODE PRODUKSI</th>
                                            <td colspan="5">{{ $detail->production_code ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">NOMOR FORMULA</th>
                                            <td colspan="5">{{ $detail->formula->formula_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">WAKTU MIXING</th>
                                            <td colspan="5">{{ $detail->mixing_time ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">NAMA MESIN MIXER/CHOPPER</th>
                                            <td colspan="5">{{ $detail->machine_name ?? '-' }}</td>
                                        </tr>

                                        {{-- A. BAHAN BAKU --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">A. BAHAN BAKU</td>
                                        </tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Bahan</th>
                                            <th>Berat (kg)</th>
                                            <th>Sensorik</th>
                                            <th>Kode Produksi</th>
                                            <th>Suhu (℃)</th>
                                            <th>Keterangan</th>
                                        </tr>
                                        @php $i = 1; @endphp
                                        @foreach ($detail->items->filter(fn($item) => $item->material_type
                                            ? $item->material_type === 'raw_material'
                                            : $item->formulation?->raw_material_uuid) as $item)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $item->material_name ?? $item->formulation?->rawMaterial?->material_name ?? '-' }}</td>
                                            <td>{{ $item->actual_weight }}</td>
                                            <td>{{ $item->sensory }}</td>
                                            <td>{{ $item->prod_code }}</td>
                                            <td>{{ $item->temperature }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                        </tr>
                                        @endforeach

                                        {{-- B. PREMIX --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">B. PREMIX / BAHAN TAMBAHAN</td>
                                        </tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Bahan</th>
                                            <th>Berat (kg)</th>
                                            <th>Sensorik</th>
                                            <th>Kode Produksi</th>
                                            <th>Suhu (℃)</th>
                                            <th>Keterangan</th>
                                        </tr>
                                        @php $j = 1; @endphp
                                        @foreach ($detail->items->filter(fn($item) => $item->material_type
                                            ? $item->material_type === 'premix'
                                            : ($item->formulation && !$item->formulation->raw_material_uuid)) as $item)
                                        <tr>
                                            <td>{{ $j++ }}</td>
                                            <td>{{ $item->material_name ?? $item->formulation?->premix?->name ?? '-' }}</td>
                                            <td>{{ $item->actual_weight }}</td>
                                            <td>{{ $item->sensory }}</td>
                                            <td>{{ $item->prod_code }}</td>
                                            <td>{{ $item->temperature }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                        </tr>
                                        @endforeach

                                        <tr>
                                            <th colspan="2">Hasil Penggilingan</th>
                                            <td colspan="5">{{ $detail->hasil_penggilingan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Hasil Pencampuran</th>
                                            <td colspan="5">{{ $detail->hasil_pencampuran ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">REWORK (kg/%)</th>
                                            <td colspan="5">{{ $detail->rework_kg ?? '-' }} /
                                                {{ $detail->rework_percent ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">PRODUK REWORK</th>
                                            <td colspan="5">{{ $detail->reworkProduct->product_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">TOTAL BAHAN (kg)</th>
                                            <td colspan="5">{{ $detail->total_material ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Catatan After Rework</th>
                                            <td colspan="5">{{ $detail->notes ?? '-' }}</td>
                                        </tr>

                                        {{-- C. EMULSIFYING --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">C. EMULSIFYING</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Standar suhu adonan (℃)</th>
                                            <td colspan="5">{{ $detail->emulsifying->standard_mixture_temp ?? '14 ± 2' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Aktual suhu adonan (℃)</th>
                                            <td colspan="5">
                                                {{ $detail->emulsifying->actual_mixture_temp_1 ?? '-' }} /
                                                {{ $detail->emulsifying->actual_mixture_temp_2 ?? '-' }} /
                                                {{ $detail->emulsifying->actual_mixture_temp_3 ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Rata-rata suhu adonan (℃)</th>
                                            <td colspan="5">{{ $detail->emulsifying->average_mixture_temp ?? '-' }}</td>
                                        </tr>

                                        {{-- D. SENSORIK --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">D. SENSORIK</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Homogenitas</th>
                                            <td colspan="5">{{ $detail->sensoric->homogeneous ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Kekentalan</th>
                                            <td colspan="5">{{ $detail->sensoric->stiffness ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Aroma</th>
                                            <td colspan="5">{{ $detail->sensoric->aroma ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Benda Asing</th>
                                            <td colspan="5">{{ $detail->sensoric->foreign_object ?? '-' }}</td>
                                        </tr>

                                        {{-- E. TUMBLING --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">E. TUMBLING</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Proses Tumbling</th>
                                            <td colspan="5">{{ $detail->tumbling->tumbling_process ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Lama Proses (Menit)</th>
                                            <td colspan="5">{{ $detail->tumbling->process_duration ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Suhu Akhir Tumbling (°C)</th>
                                            <td colspan="5">{{ $detail->tumbling->final_temperature ?? '-' }}</td>
                                        </tr>

                                        {{-- F. AGING --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">F. AGING</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Proses Aging</th>
                                            <td colspan="5">{{ $detail->aging->aging_process ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Hasil Stuffing</th>
                                            <td colspan="5">{{ $detail->aging->stuffing_result ?? '-' }}</td>
                                        </tr>
                                    </table>

                                    <p>Catatan: {{ $report->notes ?? '-' }}</p>
                                    <hr> <br><br>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">Belum ada data.</td>
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