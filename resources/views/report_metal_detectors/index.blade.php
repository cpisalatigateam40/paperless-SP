@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Metal Detector Adonan</h4>
            <a href="{{ route('report_metal_detectors.create') }}" class="btn btn-sm btn-primary">Tambah Report</a>
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

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Ketidaksesuaian</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
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
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report_metal_detectors.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus --}}
                                <form action="{{ route('report_metal_detectors.destroy', $report->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_metal_detectors.known', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                        <i class="fas fa-eye"></i>
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
                                <form action="{{ route('report_metal_detectors.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report_metal_detectors.export_pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Jam</th>
                                                <th>Produk</th>
                                                <th>Gramase</th>
                                                <th>Kode Produksi</th>
                                                <th>Fe 1.5 mm</th>
                                                <th>Non Fe 1.5 mm</th>
                                                <th>SUS 316 2.5 mm</th>
                                                <th>Hasil Verifikasi MD Loma</th>
                                                <th>Keterangan</th>
                                                <th>Ketidaksesuaian</th>
                                                <th>Tindakan Koreksi</th>
                                                <th>Verifikasi Setelah Tindakan Koreksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->hour }}</td>
                                                <td>{{ $detail->product->product_name ?? '-' }}</td>
                                                <td>{{ $detail->product->nett_weight ?? '-' }} g</td>
                                                <td>{{ $detail->production_code }}</td>
                                                <td>{{ $detail->result_fe }}</td>
                                                <td>{{ $detail->result_non_fe }}</td>
                                                <td>{{ $detail->result_sus316 }}</td>
                                                <td>{{ $detail->verif_loma }}</td>
                                                <td>{{ $detail->notes }}</td>
                                                <td>{{ $detail->nonconformity }}</td>
                                                <td>{{ $detail->corrective_action }}</td>
                                                <td>{{ $detail->verif_after_correct }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center">Tidak ada detail</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <div class="mt-2 d-flex justify-content-end">
                                        <a href="{{ route('report_metal_detectors.add_detail', $report->uuid) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            Tambah Detail Pemeriksaan
                                        </a>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
@endsection