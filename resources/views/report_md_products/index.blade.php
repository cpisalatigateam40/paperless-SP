@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Verifikasi Kinerja Metal Detector Produk</h5>

            <div class="d-flex gap-2" style="gap: .4rem;">

                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_md_products.index') }}">
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
                    action="{{ route('report_md_products.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

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
                    @if(request('search'))
                        <a href="{{ route('report_md_products.index') }}"
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
                    :route="route('report_md_products.export_pdf_bulk')"
                    title="MD Product"
                    modal-id="modalExportPdfMdProduct"
                    :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                />

                {{-- Modals --}}
                @role('Produksi')
                <x-bulk-approval-modal
                    prefix="known"
                    title="Produksi"
                    color="warning"
                    icon="fa-check-double"
                    action-route="report-md-products.bulk-known"
                    count-route="report-md-products.bulk-known-count"
                    label="Approve Semua"
                />
                @endrole

                @role('SPV QC')
                <x-bulk-approval-modal
                    prefix="approve"
                    title="QC"
                    color="success"
                    icon="fa-check-circle"
                    action-route="report-md-products.bulk-approve"
                    count-route="report-md-products.bulk-approve-count"
                    label="Approve Semua"
                />
                @endrole

                <x-export-excel-modal
                    :route="route('report_md_products.export')"
                    title="Verifikasi Metal Detector Produk" />

                @can('create report')
                <a href="{{ route('report_md_products.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                            <th>Metal Detector</th>
                            <th>Kode Produksi</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name }}</td>
                            <td>
                                @if ($report->metalDetector)
                                    {{ $report->metalDetector->merk }} - {{ $report->metalDetector->type_model }}
                                    <br><small class="text-muted">{{ $report->metalDetector->no_series }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            @php
                                $codes = $report->details->pluck('production_code')->filter()->implode(', ');
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
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                <div class="d-flex" style="gap: .2rem;">
                                
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(8));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_md_products.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form method="POST" action="{{ route('report_md_products.destroy', $report->uuid) }}"
                                    onsubmit="return confirm('Hapus report ini?')">
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
                                <form action="{{ route('report_md_products.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </form>
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
                                <form action="{{ route('report_md_products.approve', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                </form>
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
                                <a href="{{ route('report_md_products.export-pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                </div>
                            </td>

                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="10">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle">No</th>
                                                <th rowspan="2" class="align-middle">Waktu Verifikasi</th>
                                                <th rowspan="2" class="align-middle">Nama Produk</th>
                                                <th rowspan="2" class="align-middle">Gramase (gr)</th>
                                                <th rowspan="2" class="align-middle">Kode Produksi</th>
                                                <th colspan="3" class="align-middle">Fe 1.5 mm</th>
                                                <th colspan="3" class="align-middle">Non-Fe 2.0 mm</th>
                                                <th colspan="3" class="align-middle">SUS 2.5 mm</th>
                                                <th rowspan="2" class="align-middle">Status (OK/NG)</th>
                                                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                                <th rowspan="2" class="align-middle">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <th>D</th>
                                                <th>T</th>
                                                <th>B</th>
                                                <th>D</th>
                                                <th>T</th>
                                                <th>B</th>
                                                <th>D</th>
                                                <th>T</th>
                                                <th>B</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($report->details as $detail)
                                            @php
                                                $specimens = ['fe_1_5mm', 'non_fe_2mm', 'sus_2_5mm'];
                                                $positions = ['d', 't', 'b'];
                                                $rowOk = !$detail->positions->contains('status', false);
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($detail->time)->format('H:i') }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ $detail->gramase ?? '-' }}</td>
                                                <td>{{ $detail->production_code ?? '-' }}</td>
                                                @foreach ($specimens as $specimen)
                                                    @foreach ($positions as $pos)
                                                    @php
                                                        $posDetail = $detail->positions
                                                            ->where('specimen', $specimen)
                                                            ->where('position', $pos)
                                                            ->first();
                                                    @endphp
                                                    <td>{{ $posDetail ? ($posDetail->status ? '✓' : '×') : '-' }}</td>
                                                    @endforeach
                                                @endforeach
                                                <td>
                                                    <span class="badge {{ $detail->status ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $detail->status ? 'OK' : 'NG' }}
                                                    </span>
                                                </td>
                                                <td>{{ $detail->corrective_action ?: '-' }}</td>
                                                <td>{{ $detail->verification ?: '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="17" class="text-center">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <p class="mt-2">Catatan: {{ $report->notes ?: '-' }}</p>
                                    @can('create report')
                                    <div class="mt-2 d-flex justify-content-end">
                                        <a href="{{ route('report_md_products.add-detail', $report->uuid) }}"
                                            class="btn btn-sm btn-secondary">
                                            Tambah Detail Pemeriksaan
                                        </a>
                                    </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada report</td>
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