@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Laporan Verifikasi Barang Mudah Pecah</h5>
                <a href="{{ route('report-fragile-item.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
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
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Waktu</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $index => $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td class="d-flex" style="gap: .2rem;">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-info btn-sm toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Update --}}
                                <a href="{{ route('report-fragile-item.edit', $report->uuid) }}"
                                    class="btn btn-warning btn-sm" title="Update">
                                    <i class="fas fa-pen"></i>
                                </a>

                                @can('edit report')
                                <a href="{{ route('report-fragile-item.edit-next', $report->uuid) }}"
                                    class="btn btn-sm btn-danger" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus --}}
                                <form action="{{ route('report-fragile-item.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                {{-- Known --}}
                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report-fragile-item.known', $report->id) }}" method="POST"
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
                                <form action="{{ route('report-fragile-item.approve', $report->id) }}" method="POST"
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
                                <a href="{{ route('report-fragile-item.export', $report->uuid) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>

                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="7">
                                @php
                                $groupedDetails = $report->details->groupBy(fn($d) => $d->item->section_name);
                                $no = 1;
                                @endphp

                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Pemilik (Area)</th>
                                            <th>Jumlah</th>
                                            <th>Waktu Awal</th>
                                            <th>Waktu Akhir</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupedDetails as $section => $items)
                                        <tr>
                                            <td colspan="7"><strong>{{ $section }}</strong></td>
                                        </tr>
                                        @foreach ($items as $detail)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $detail->item->item_name }}</td>
                                            <td>{{ $detail->item->owner }}</td>
                                            <td>{{ $detail->item->quantity }}</td>
                                            <td>{{ $detail->time_start == '1' ? '✓' : '' }}</td>
                                            <td>{{ $detail->time_end == '1' ? '✓' : '' }}</td>
                                            <td>{{ $detail->notes == '1' ? '✓' : '' }}</td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{ $reports->links('pagination::bootstrap-5') }} {{-- Pagination --}}
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

    // Toggle Detail
    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('.toggle-detail').not(this).text('Lihat Detail'); // reset label
        $('tr[id^="detail-"]').addClass('d-none'); // hide all

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