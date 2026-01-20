@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Premix</h4>
            <a href="{{ route('report-premixes.create') }}" class=" btn-primary btn-sm">Tambah Laporan</a>
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
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Waktu</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Ketidaksesuaian</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $i => $report)
                        <tr>
                            <td class="align-middle">{{ $i + $reports->firstItem() }}</td>
                            <td class="align-middle">{{ $report->date->format('d-m-Y') }}</td>
                            <td class="align-middle">{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                            <td>
                                @if ($report->ketidaksesuaian > 0)
                                Ada
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $report->created_by }}</td>
                            <td class="align-middle">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- @can('edit report')
                                <a href="{{ route('report-premixes.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan -->

                                @php
                                    $user = auth()->user();
                                    $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ route('report-premixes.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                {{-- Hapus --}}
                                <form action="{{ route('report-premixes.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-premixes.known', $report->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Ketahui laporan ini?')">
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
                                <form action="{{ route('report-premixes.approve', $report->id) }}" method="POST"
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

                                {{-- Export PDF --}}
                                <a href="{{ route('report-premixes.exportPdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="align-middle">Nama Premix</th>
                                            <th class="align-middle">Kode Produksi</th>
                                            <th class="align-middle">Berat (gr)</th>
                                            <th class="align-middle">Batch</th>
                                            <th class="align-middle">Keterangan</th>
                                            <th class="align-middle">Tindakan Koreksi</th>
                                            <th class="align-middle">Verifikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($report->detailPremixes as $detail)
                                        <tr>
                                            <td class="align-middle">{{ $detail->premix->name ?? '-' }}</td>
                                            <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>

                                            <td class="align-middle text-end">{{ $detail->weight }}</td>
                                            <td class="align-middle">{{ $detail->used_for_batch }}</td>
                                            <td class="align-middle">{{ $detail->notes }}</td>
                                            <td class="align-middle">{{ $detail->corrective_action }}</td>
                                            <td class="align-middle text-center">
                                                {{ $detail->verification }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>



            <div class="mt-3">
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