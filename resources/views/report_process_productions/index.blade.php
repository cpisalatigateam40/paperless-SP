@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Verifikasi Proses Mixing, Chopping, dan Emulsifying</h5>
            
            <div class="d-flex gap-2" style="gap: .4rem;">

                @hasanyrole('admin|superadmin')
                    <form method="GET" action="{{ route('report_process_productions.index') }}">
                        <input type="hidden" name="section" value="{{ request('section') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        <select name="area"
                                class="form-select form-control form-control"
                                onchange="this.form.submit()">
                            <option value="">Semua Area</option>

                            @foreach($areas as $area)
                                <option value="{{ $area->uuid }}"
                                    {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endhasanyrole

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('report_process_productions.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari laporan..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('section'))
                        <a href="{{ route('report_process_productions.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
                            Reset
                        </a>
                    @endif

                </form>

                {{-- Buttons --}}
                <div class="d-flex gap-2">
                    @role('Produksi')
                    <button type="button" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalBulkKnown">
                        <i class="fas fa-check-double"></i> Approve (Produksi)
                    </button>
                    @endrole

                    @role('SPV QC')
                    <button type="button" class="btn btn-success btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalBulkApprove">
                        <i class="fas fa-check-circle"></i> Approve (QC)
                    </button>
                    @endrole
                </div>

                <x-export-pdf-modal
                    :route="route('report-process-productions.bulk-export-pdf')"
                    title="Process Production"
                    modal-id="modalExportPdfProcessProduction"
                    :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal
                    prefix="known"
                    title="Produksi"
                    color="warning"
                    icon="fa-check-double"
                    action-route="report-process-productions.bulk-known"
                    count-route="report-process-productions.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="report-process-productions.bulk-approve"
                    count-route="report-process-productions.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                <x-export-excel-modal 
                    :route="route('report_process_productions.export_excel')" 
                    title="Verifikasi Proses Produksi" />

                @can('create report')
                <a href="{{ route('report_process_productions.create') }}" class="btn btn-primary btn-sm">Tambah
                Laporan</a>
                @endcan
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
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

            <x-report-sort
                :sort-options="[
                    'latest' => 'Terbaru',
                    'production_code' => 'Kode Produksi',
                    'report_date' => 'Tanggal Laporan',
                    'submitted_at' => 'Tanggal Submit',
                ]"
                :with-date-filter="true"
            />

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
                        @foreach ($reports as $report)
                        @php
                        $detail = $report->detail->first();
                        @endphp
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $detail?->product?->product_name ?? '-' }}</td>
                            @php
                                $codes = $report->detail->pluck('production_code')->filter()->implode(', ');
                                $collapseId = 'codes-' . $report->uuid;
                            @endphp

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
                            <td>
                                {{ $detail->formula->formula_name ?? '-' }}
                            </td>
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->uuid }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report_process_productions.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_process_productions.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report_process_productions.destroy', $report->uuid) }}"
                                    method="POST" onsubmit="return confirm('Hapus laporan ini?')">
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
                                <form action="{{ route('report_process_productions.known', $report->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Ketahui laporan ini?')">
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
                                <form action="{{ route('report_process_productions.approve', $report->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Setujui laporan ini?')">
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

                                {{-- Export PDF --}}
                                <a href="{{ route('report_process_productions.export', $report->uuid) }}"
                                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        {{-- DETAIL COLLAPSIBLE --}}
                        <tr class="collapse" id="detail-{{ $report->uuid }}">
                            <td colspan="100%">
                                <div class="mt-3">
                                    @foreach ($report->detail as $detail)
                                    <table class="table table-bordered table-sm mb-2">
                                        {{-- HEADER PRODUK --}}
                                        <tr>
                                            <th colspan="2">NAMA PRODUK</th>
                                            <td colspan="5">{{ $detail->product->product_name ?? '-' }}
                                            </td>
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

                                            <td colspan="5">{{$detail->hasil_penggilingan ?? '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th colspan="2">Hasil Pencampuran</th>

                                            <td colspan="5">{{$detail->hasil_pencampuran ?? '-' }}</td>
                                        </tr>

                                        {{-- REWORK & TOTAL --}}
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
                                        <!-- <tr>
                                            <th colspan="2">Sensori Homogenitas</th>
                                            <td colspan="4">{{ $detail->sensory_homogenity ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Sensori Kekentalan</th>
                                            <td colspan="4">{{ $detail->sensory_stiffness ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Sensori Aroma</th>
                                            <td colspan="4">{{ $detail->sensory_aroma ?? '-' }}</td>
                                        </tr> -->
                                        <tr>
                                            <th colspan="2">Catatan After Rework</th>
                                            <td colspan="5">{{ $detail->notes ?? '-' }}</td>
                                        </tr>

                                        {{-- EMULSIFYING --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7">C. EMULSIFYING</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Standar suhu adonan (℃)</th>
                                            <td colspan="5">
                                                {{ $detail->emulsifying->standard_mixture_temp ?? '14 ± 2' }}
                                            </td>
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

                                        {{-- SENSORIK --}}
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

                                        {{-- TUMBLING --}}
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

                                        {{-- AGING --}}
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

                                    <!-- <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_process_productions.add_detail', $report->uuid) }}"
                                        class="btn btn-secondary btn-sm">
                                        + Tambah Detail
                                    </a>
                                </div> -->
                                </div>
                            </td>
                        </tr>

                        @endforeach
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
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>

<script>
function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>
@endsection