@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-middle">
            <h5>Laporan Kedatangan Bahan Baku</h5>
            <a href="{{ route('report_rm_arrivals.create') }}" class="btn btn-sm btn-success">+ Tambah Laporan</a>
        </div>

        <div class="card-body">
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

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Section</th>
                        <th>Dibuat oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section?->section_name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>
                            {{-- Toggle Detail --}}
                            <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Hapus --}}
                            <form action="{{ route('report_rm_arrivals.destroy', $report->uuid) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_rm_arrivals.known', $report->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Ketahui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
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
                            <form action="{{ route('report_rm_arrivals.approve', $report->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Setujui laporan ini?')">
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
                            <a href="{{ route('report_rm_arrivals.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary" title="Export PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>


                    </tr>
                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="6">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th rowspan="2" class="align-middle">Jam</th>
                                        <th rowspan="2" class="align-middle">Raw Material</th>
                                        <th rowspan="2" class="align-middle">Produsen / Supplier</th>
                                        <th rowspan="2" class="align-middle">Kode Produksi / Expired Date</th>
                                        <th rowspan="2" class="align-middle">Kondisi Kemasan</th>
                                        <th colspan="2" class="align-middle">Kondisi Bahan</th>
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
                                        <td>{{ $detail->supplier ?? '-' }}</td>
                                        <td>{{ $detail->production_code ?? '-' }}</td>
                                        <td class="text-center">{{ $detail->packaging_condition }}</td>
                                        <td class="text-center">{{ $detail->temperature }}</td>
                                        <td class="text-center">{{ $detail->sensorial_condition }}</td>
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
                        <td colspan="5" class="text-center">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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

        $('.toggle-detail').not(this).text('Lihat Detail');
        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
            $(this).text('Sembunyikan Detail');
        } else {
            target.addClass('d-none');
            $(this).text('Lihat Detail');
        }
    });
});
</script>
@endsection