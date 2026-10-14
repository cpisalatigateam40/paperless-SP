@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Verifikasi Proses Thawing</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_thawings.index') }}"
                    class="op-item op-form">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area" class="form-control form-control-sm" onchange="this.form.submit()">
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

                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_thawings.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search'))
                        <a href="{{ route('report_thawings.index') }}"
                            class="btn btn-sm btn-danger" title="Reset Filter">Reset</a>
                    @endif
                </form>

                {{-- Approve --}}
                @role('Produksi')
                <div class="op-item">
                    <button type="button" class="btn btn-warning btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkKnown">
                        <i class="fas fa-check-double"></i> Approve (Produksi)
                    </button>
                </div>
                @endrole

                @role('SPV QC')
                <div class="op-item">
                    <button type="button" class="btn btn-success btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBulkApprove">
                        <i class="fas fa-check-circle"></i> Approve (QC)
                    </button>
                </div>
                @endrole

                <div class="op-divider d-none d-lg-block"></div>

                {{-- Export --}}
                <div class="op-item">
                    <x-export-pdf-modal
                        :route="route('report_thawings.export_pdf_bulk')"
                        title="Thawing"
                        modal-id="modalExportPdfThawing"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('report_thawings.export')"
                        title="Pemeriksaan Thawing" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report_thawings.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('report_thawings.audit') }}" class="btn btn-sm btn-audit">
                        <i class="fas fa-user-shield"></i> Audit
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Modals: di luar toolbar --}}
        @role('Produksi')
        <x-bulk-approval-modal
            prefix="known"
            title="Produksi"
            color="warning"
            icon="fa-check-double"
            action-route="report-thawings.bulk-known"
            count-route="report-thawings.bulk-known-count"
            label="Approve Semua"
        />
        @endrole

        @role('SPV QC')
        <x-bulk-approval-modal
            prefix="approve"
            title="QC"
            color="success"
            icon="fa-check-circle"
            action-route="report-thawings.bulk-approve"
            count-route="report-thawings.bulk-approve-count"
            label="Approve Semua"
        />
        @endrole


        <div class="card-body" style="padding-top: 1rem !important;">

            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
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

                <table class="table table-bordered table-hover">

                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Kode Produksi</th>
                            <th>Dibuat Oleh</th>
                            <th width="400">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($reports as $i => $report)

                        <tr>

                            <td>{{ $i + $reports->firstItem() }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}
                            </td>

                            <td class="text-center">
                                {{ $report->shift }}
                            </td>

                            <td>
                                {{ $report->created_at->format('H:i') }}
                            </td>

                            <td>
                                {{ $report->area->name ?? '-' }}
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
                                {{ $report->created_by }}
                            </td>

                            <td>

                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->uuid }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @php
                                $user = auth()->user();
                                $canEdit = $user->hasRole(['admin', 'SPV QC']) ||
                                $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                <a href="{{ route('report_thawings.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_thawings.known', $report->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Ketahui laporan ini?')">
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
                                <form action="{{ route('report_thawings.approve', $report->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Setujui laporan ini?')">
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

                                @can('delete report')
                                <form action="{{ route('report_thawings.destroy',$report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>
                                @endcan

                                <a href="{{ route('report_thawings.export_pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_thawings" />
                                @endhasanyrole -->

                            </td>

                        </tr>


                        {{-- DETAIL ROW --}}
                        <tr id="detail-{{ $report->uuid }}" class="d-none">

                            <td colspan="9">

                                <div class="table-responsive">

                                    <table class="table table-sm table-bordered mb-0">

                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Waktu Thawing Awal</th>
                                                <th>Waktu Thawing Akhir</th>
                                                <th>Kondisi Awal Kemasan RM (utuh/sobek)</th>
                                                <th>Nama Bahan Baku</th>
                                                <th>Kode Produksi</th>
                                                <th>Jumlah</th>
                                                <th>Kondisi Ruang</th>
                                                <th>Waktu Pemeriksaan</th>
                                                <th>Suhu Ruang (&deg;C)</th>
                                                <th>Suhu Air Thawing(&deg;C)</th>
                                                <th>Suhu Produk (&deg;C)</th>
                                                <th>Kondisi Produk</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse($report->details as $detail)

                                            <tr>

                                                <td>
                                                    {{ $detail->start_thawing_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->end_thawing_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->package_condition) ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->rawMaterial->material_name ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->production_code ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->qty ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->room_condition) ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->inspection_time ?? '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->room_temp ? $detail->room_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->water_temp ? $detail->water_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ $detail->product_temp ? $detail->product_temp.' °C' : '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($detail->product_condition) ?? '-' }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="12" class="text-center text-muted">
                                                    Tidak ada detail
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                    @can('create report')
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('report_thawings.details.create', $report->uuid) }}"
                                            class="btn btn-sm btn-secondary mt-2">
                                            + Tambah Detail
                                        </a>
                                    </div>
                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tidak ada data laporan
                            </td>
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
setTimeout(function() {
    $('#success-alert').fadeOut('slow')
}, 3000)

$('.toggle-detail').click(function() {
    const target = $($(this).data('target'))
    $('tr[id^="detail-"]').not(target).addClass('d-none')
    target.toggleClass('d-none')
})
</script>

<script>
function toggleCodes(id) {
    document.getElementById(id + '-short').classList.toggle('d-none');
    document.getElementById(id + '-full').classList.toggle('d-none');
}
</script>

@endsection