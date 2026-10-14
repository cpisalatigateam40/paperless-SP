@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-lg-0 mr-3">Verifikasi Proses Pembuatan Emulsi</h5>

            <div class="op-toolbar d-flex flex-row flex-wrap align-items-center">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('report_emulsion_makings.index') }}"
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
                <form method="GET" action="{{ route('report_emulsion_makings.index') }}"
                    class="op-item op-form op-search d-flex flex-row flex-wrap align-items-center">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input type="text" name="search"
                        class="form-control form-control-sm op-search-input"
                        placeholder="Cari laporan..." value="{{ request('search') }}">

                    <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>

                    @if(request('search') || request('section'))
                        <a href="{{ route('report_emulsion_makings.index') }}"
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
                        :route="route('report_emulsion_makings.export_pdf_bulk')"
                        title="Emulsion Making"
                        modal-id="modalExportPdfEmulsionMaking"
                        :shift-options="['1' => 'Shift 1', '2' => 'Shift 2', '3' => 'Shift 3']"
                    />
                </div>

                <div class="op-item">
                    <x-export-excel-modal
                        :route="route('report_emulsion_makings.export')"
                        title="Verifikasi Pembuatan Emulsi" />
                </div>

                @can('create report')
                <div class="op-item">
                    <a href="{{ route('report_emulsion_makings.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                @endcan

                @hasanyrole('admin|superadmin|SPV QC')
                <div class="op-item">
                    <a href="{{ route('report_emulsion_makings.audit') }}" class="btn btn-sm btn-audit">
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
            action-route="report-emulsion-makings.bulk-known"
            count-route="report-emulsion-makings.bulk-known-count"
            label="Approve Semua"
        />
        @endrole

        @role('SPV QC')
        <x-bulk-approval-modal
            prefix="approve"
            title="QC"
            color="success"
            icon="fa-check-circle"
            action-route="report-emulsion-makings.bulk-approve"
            count-route="report-emulsion-makings.bulk-approve-count"
            label="Approve Semua"
        />
        @endrole

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
                            <th>Kode Produksi</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ optional($report->area)->name }}</td>
                            <td>{{ $report->header->production_code }}</td>
                            
                            
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td >
                                <div class="d-flex" style="gap: .2rem;">

                                
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report_emulsion_makings.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_emulsion_makings.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @can('delete report')
                                <form action="{{ route('report_emulsion_makings.destroy', $report->uuid) }}"
                                    method="POST" onsubmit="return confirm('Yakin hapus?')">
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
                                <form action="{{ route('report_emulsion_makings.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
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
                                <form action="{{ route('report_emulsion_makings.approve', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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
                                <a href="{{ route('report_emulsion_makings.export-pdf', $report->uuid) }}"
                                    class="btn btn-secondary btn-sm" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report_emulsion_makings" />
                                @endhasanyrole -->
                                </div>
                            </td>

                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="100%">
                                <div class="table-responsive p-2">
                                    <table class="table table-sm table-bordered text-center">
                                        <thead>
                                            {{-- Baris 1: Jenis Emulsi --}}
                                            <tr>
                                                <th style="width: 200px;" colspan="2">JENIS EMULSI</th>
                                                @foreach($report->header->agings ?? [] as $idx => $aging)
                                                <td colspan="2">{{ $report->header->emulsion_type ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            {{-- Baris 2: Kode Produksi --}}
                                            <tr>
                                                <th colspan="2">KODE PRODUKSI</th>
                                                @foreach($report->header->agings ?? [] as $idx => $aging)
                                                <td colspan="2">{{ $report->header->production_code ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            {{-- Baris 3: header kolom detail --}}
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
                                            {{-- Detail bahan baku --}}
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


                                            {{-- Start aging --}}
                                            <tr>
                                                <td colspan="2">Waktu Awal Proses</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->start_aging ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            {{-- Finish aging --}}
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
                                            {{-- Hasil emulsi (sensory) --}}
                                            <tr>
                                                <td colspan="2">Hasil emulsi (sensory)</td>
                                                @foreach($report->header->agings ?? [] as $aging)
                                                <td colspan="2">{{ $aging->emulsion_result ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>

                                    @can('create report')
                                    <div class="d-flex justify-content-end mt-2">
                                        <a href="{{ route('report_emulsion_makings.add-detail', $report->uuid) }}"
                                            class="btn btn-secondary btn-sm">
                                            Tambah Detail
                                        </a>
                                    </div>
                                    @endcan
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