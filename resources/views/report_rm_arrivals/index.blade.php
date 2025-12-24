@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-middle">
            <h5>Laporan Verifikasi Kedatangan Bahan Baku dan Bahan Penunjang</h5>
            <a href="{{ route('report_rm_arrivals.create') }}" class="btn btn-sm btn-primary">Tambah Laporan</a>
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

            {{-- Nav Tabs --}}
            <ul class="nav nav-tabs mb-3" id="sectionTab" role="tablist">
                @foreach($reports as $section => $items)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($loop->first) active @endif" id="tab-{{ Str::slug($section) }}"
                        data-bs-toggle="tab" data-bs-target="#content-{{ Str::slug($section) }}" type="button"
                        role="tab">
                        {{ $section }}
                    </button>
                </li>
                @endforeach
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="sectionTabContent">
                @foreach($reports as $section => $items)
                <div class="tab-pane fade @if($loop->first) show active @endif" id="content-{{ Str::slug($section) }}"
                    role="tabpanel">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Shift</th>
                                    <th>Waktu</th>
                                    <th>Area</th>
                                    <th>Ketidaksesuaian</th>
                                    <th>Dibuat oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $report)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                    <td>{{ $report->shift }}</td>
                                    <td>{{ $report->created_at->format('H:i') }}</td>
                                    <td>{{ $report->area->name ?? '-' }}</td>
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

                                        @can('edit report')
                                        <a href="{{ route('report_rm_arrivals.edit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning" title="Edit Laporan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        {{-- Hapus --}}
                                        <form action="{{ route('report_rm_arrivals.destroy', $report->uuid) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

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

                                {{-- Detail Row --}}
                                <tr id="detail-{{ $report->id }}" class="d-none">
                                    <td colspan="7">
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
                                                    <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
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
                                        <div class="mb-2 d-flex justify-content-end mt-3">
                                            <a href="{{ route('report_rm_arrivals.add_detail', $report->uuid) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                + Tambah Pemeriksaan
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>



                </div>
                @endforeach
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