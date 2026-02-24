@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Laporan Verifikasi Kedatangan Bahan Baku dan Bahan Penunjang
            </h5>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- FILTER SECTION --}}
                <form method="GET" action="{{ route('report_rm_arrivals.index') }}">
                    <select name="section" class="form-select form-control-sm form-control" onchange="this.form.submit()">
                        <option value="">Semua Section</option>
                        <option value="Chillroom" {{ request('section') == 'Chillroom' ? 'selected' : '' }}>Chillroom</option>
                        <option value="Seasoning" {{ request('section') == 'Seasoning' ? 'selected' : '' }}>Seasoning</option>
                    </select>
                </form>

                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report_rm_arrivals.index') }}" class="d-flex align-items-center gap-1" style="gap: .4rem;;">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control form-control-sm mr-2"
                            placeholder="Cari laporan..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                    </div>
                    @if(request('search') || request('section'))
                        <a href="{{ route('report_rm_arrivals.index') }}" class="btn btn-sm btn-outline-danger">Reset</a>
                    @endif
                </form>

                <div class="vr"></div>

                @can('import report')
                <form action="{{ route('report_rm_arrivals.import') }}" method="POST"
                    enctype="multipart/form-data" class="d-flex align-items-center gap-1">
                    @csrf
                    <select name="section_uuid" class="form-select form-control-sm form-control mr-2" required>
                        <option value="">-- Section --</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                    <label class="btn btn-sm btn-outline-secondary mb-0" style="cursor:pointer;">
                        <i class="bi bi-upload"></i> Import
                        <input type="file" name="file" required hidden
                            onchange="this.closest('form').querySelector('#btnImport').click()">
                    </label>
                    <button id="btnImport" type="submit" class="d-none"></button>
                </form>

                {{-- DOWNLOAD TEMPLATE --}}
                <a href="{{ route('report_rm_arrivals.template') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download"></i> Template
                </a>
                @endcan

                <div class="vr"></div>

                @can('create report')
                <a href="{{ route('report_rm_arrivals.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
                @endcan

            </div>
        </div>


        <div class="card-body">
            {{-- Alert --}}
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Section</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                    Ada
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report_rm_arrivals.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->
                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report_rm_arrivals.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif



                                @can('delete report')
                                <form action="{{ route('report_rm_arrivals.destroy', $report->uuid) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_rm_arrivals.known', $report->id) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                        title="Diketahui">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    ✔ {{ $report->known_by }}
                                </span>
                                @endif
                                @else
                                @if($report->known_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    ✔ {{ $report->known_by }}
                                </span>
                                @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                @if(!$report->approved_by)
                                <form action="{{ route('report_rm_arrivals.approve', $report->id) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Setujui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    ✔ {{ $report->approved_by }}
                                </span>
                                @endif
                                @else
                                @if($report->approved_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    ✔ {{ $report->approved_by }}
                                </span>
                                @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report_rm_arrivals.export-pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Export PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>

                        {{-- DETAIL --}}
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr class="text-center align-middle">
                                            <th rowspan="2" class="align-middle">Jam</th>
                                            <th rowspan="2" class="align-middle">Raw Material</th>
                                            <th rowspan="2" class="align-middle">Kondisi RM</th>
                                            <th rowspan="2" class="align-middle">Produsen / Supplier</th>
                                            <th rowspan="2" class="align-middle">Kode Produksi / Expired Date
                                            </th>
                                            <th rowspan="2" class="align-middle">Kondisi Kemasan</th>
                                            <th colspan="2" class="align-middle">Kondisi Bahan</th>
                                            <th rowspan="2" class="align-middle">Kontaminasi</th>
                                            <th rowspan="2" class="align-middle">Problem</th>
                                            <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                        </tr>
                                        <tr class="text-center align-middle">
                                            <th class="align-middle">Suhu Bahan (°C)</th>
                                            <th class="align-middle">Sensorik</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($report->details as $i => $detail)
                                        <tr>
                                            <td class="text-center">{{ $detail->time ?? '-' }}</td>
                                            <td>
                                            @if ($detail->material_type === 'raw')
                                                {{ $detail->rawMaterial?->material_name }}
                                            @else
                                                {{ $detail->premix?->name }} (Premix)
                                            @endif
                                            </td>
                                            <td class="text-center">{{ $detail->rm_condition }}</td>
                                            <td>{{ implode(', ', explode(',', $detail->supplier)) }}</td>
                                            <td>{{ $detail->production_code ?? '-' }}</td>
                                            <td class="text-center">{{ $detail->packaging_condition }}</td>
                                            <td class="text-center">{{ $detail->temperature }}</td>
                                            <td class="text-center">
                                                Kenampakan: {{ $detail->sensory_appearance }},
                                                Aroma: {{ $detail->sensory_aroma }},
                                                Warna: {{ $detail->sensory_color }}
                                            </td>
                                            <td class="text-center">{{ $detail->contamination }}</td>
                                            <td>{{ $detail->problem ?? '-' }}</td>
                                            <td>{{ $detail->corrective_action ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @can('create report')
                                <div class="mb-2 d-flex justify-content-end mt-3">
                                    <a href="{{ route('report_rm_arrivals.add_detail', $report->uuid) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        + Tambah Pemeriksaan
                                    </a>
                                </div>
                                @endcan
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $reports->links('pagination::bootstrap-5') }}
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

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        // Tutup semua detail lain
        $('tr[id^="detail-"]').addClass('d-none');
        $('.toggle-detail').html('<i class="fas fa-eye"></i>');

        if (isHidden) {
            target.removeClass('d-none');
            $(this).html('<i class="fas fa-eye-slash"></i>');
        }
    });
});
</script>
@endsection