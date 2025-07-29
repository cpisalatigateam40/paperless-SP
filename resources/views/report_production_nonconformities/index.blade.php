@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Ketidaksesuaian Proses Produksi</h4>
            <a href="{{ route('report_production_nonconformities.create') }}" class="btn btn-sm btn-primary">
                + Tambah Report
            </a>
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
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Dibuat Oleh</th>
                            <th>Jumlah Temuan</th>
                            <th>Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td>{{ $report->details->count() }}</td>
                            <td>{{ optional($report->area)->name ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}">
                                    Lihat Detail
                                </button>

                                <form action="{{ route('report_production_nonconformities.destroy', $report->uuid) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Yakin ingin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>

                                @can('known report')
                                @if(!$report->known_by)
                                <form action="{{ route('report_production_nonconformities.known', $report->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Ketahui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Diketahui</button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    Diketahui oleh {{ $report->known_by }}
                                </span>
                                @endif
                                @else
                                @if($report->known_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    Diketahui oleh {{ $report->known_by }}
                                </span>
                                @endif
                                @endcan

                                @can('approve report')
                                @if(!$report->approved_by)
                                <form action="{{ route('report_production_nonconformities.approve', $report->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Setujui laporan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                @else
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    Disetujui oleh {{ $report->approved_by }}
                                </span>
                                @endif
                                @else
                                @if($report->approved_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    Disetujui oleh {{ $report->approved_by }}
                                </span>
                                @endif
                                @endcan

                                <a href="{{ route('report_production_nonconformities.export-pdf', $report->uuid) }}"
                                    target="_blank" class="btn btn-sm btn-outline-secondary">
                                    🖨 Cetak PDF
                                </a>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">
                                <table class="table table-sm table-bordered mb-3 text-center">
                                    <thead>
                                        <tr>
                                            <th>Jam</th>
                                            <th>Deskripsi Ketidaksesuaian</th>
                                            <th>Jumlah</th>
                                            <th>Kategori Bahaya</th>
                                            <th>Disposisi</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $detail)
                                        <tr>
                                            <td>{{ $detail->occurrence_time }}</td>
                                            <td>{{ $detail->description }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ $detail->hazard_category }}</td>
                                            <td>{{ $detail->disposition }}</td>
                                            <td>{{ $detail->remark ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6">Tidak ada detail temuan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('report_production_nonconformities.add-detail', $report->uuid) }}"
                                        class="btn btn-sm btn-primary">+ Tambah Detail</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data</td>
                        </tr>
                        @endforelse
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

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        // Sembunyikan detail lain
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