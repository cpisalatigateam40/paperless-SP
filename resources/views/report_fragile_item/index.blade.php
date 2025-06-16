@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan Pemeriksaan Barang Mudah Pecah</h5>
                <a href="{{ route('report-fragile-item.create') }}" class="btn btn-primary btn-sm">+ Buat Laporan Baru</a>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $index => $report)
                            <tr>
                                <td>{{ $reports->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}">
                                        Lihat Detail
                                    </button>

                                    <a href="{{ route('report-fragile-item.edit', $report->uuid) }}" class="btn btn-sm btn-warning">
                                        Update Laporan
                                    </a>

                                    <form action="{{ route('report-fragile-item.destroy', $report->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>

                                    <a href="{{ route('report-fragile-item.export', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                                    @can('approve report')
                                    @if(!$report->approved_by)
                                        <form action="{{ route('report-fragile-item.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @else
                                        <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            Disetujui oleh {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->approved_by)
                                        <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            Disetujui oleh {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @endcan
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

        {{ $reports->links() }} {{-- Pagination --}}
    </div>
</div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);

        // Toggle Detail
        $('.toggle-detail').on('click', function () {
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
