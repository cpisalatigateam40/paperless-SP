@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Verifikasi Ketidaksesuaian Proses Produksi</h4>
            
            <div class="d-flex gap-2" style="gap: .4rem;">

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('report_production_nonconformities.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari laporan..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('section'))
                        <a href="{{ route('report_production_nonconformities.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
                            Reset
                        </a>
                    @endif

                </form>

                @can('create report')
                <a href="{{ route('report_production_nonconformities.create') }}" class="btn btn-sm btn-primary">
                Tambah Laporan
                </a>
                @endcan
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

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Waktu</th>
                                <th>Area</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->date }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->created_at->format('H:i') }}</td>
                                <td>{{ optional($report->area)->name ?? '-' }}</td>
                                <td>{{ $report->created_by }}</td>
                                <td class="d-flex align-items-center" style="gap: .2rem;">
                                    {{-- Toggle Detail --}}
                                    <button class="btn btn-info btn-sm toggle-detail"
                                        data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- @can('edit report')
                                    <a href="{{ route('report_production_nonconformities.edit', $report->uuid) }}"
                                        class="btn btn-sm btn-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan -->

                                    @php
                                        $user = auth()->user();
                                        $canEdit = $user->hasRole(['admin', 'SPV QC']) || $report->created_at->gt(now()->subHours(2));
                                    @endphp

                                    @if($canEdit)
                                        <a href="{{ route('report_production_nonconformities.edit', $report->uuid) }}"
                                            class="btn btn-sm btn-warning" title="Edit Laporan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    @can('delete report')
                                    <form
                                        action="{{ route('report_production_nonconformities.destroy', $report->uuid) }}"
                                        method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Yakin ingin hapus?')">
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
                                    <form action="{{ route('report_production_nonconformities.known', $report->id) }}"
                                        method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Ketahui laporan ini?')">
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
                                    <form action="{{ route('report_production_nonconformities.approve', $report->id) }}"
                                        method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Setujui laporan ini?')">
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
                                    <a href="{{ route('report_production_nonconformities.export-pdf', $report->uuid) }}"
                                        target="_blank" class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                        <i class="fas fa-file-pdf"></i>
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
                                                <th class="align-middle">Bukti</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->occurrence_time }}</td>
                                                <td>{{ $detail->description }}</td>
                                                <td>{{ $detail->quantity }} {{ $detail->unit }}</td>
                                                <td>{{ $detail->hazard_category }}</td>
                                                <td>{{ $detail->disposition }}</td>
                                                <td>
                                                    @if($detail->evidence)
                                                    <a href="{{ asset('storage/' . $detail->evidence) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/' . $detail->evidence) }}"
                                                            alt="Bukti" width="60">
                                                    </a>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->remark ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6">Tidak ada detail temuan</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    @can('create report')
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('report_production_nonconformities.add-detail', $report->uuid) }}"
                                            class="btn btn-sm btn-primary">+ Tambah Detail</a>
                                    </div>
                                    @endcan
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