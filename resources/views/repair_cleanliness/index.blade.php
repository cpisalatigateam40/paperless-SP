@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5>Laporan Pemeriksaan Kebersihan Setelah Perbaikan</h5>
            <a href="{{ route('repair-cleanliness.create') }}" class="btn btn-sm btn-primary mb-3">+ Buat Laporan</a>
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
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>{{ $report->date }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->area->name ?? '-' }}</td>
                                <td>{{ $report->created_by }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}">Lihat Detail</button>

                                    <form action="{{ route('repair-cleanliness.destroy', $report->uuid) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>

                                    <a href="{{ route('repair-cleanliness.export', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        🖨 Cetak PDF
                                    </a>

                                    @can('approve report')
                                    @if(!$report->approved_by)
                                        <form action="{{ route('repair-cleanliness.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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
                                <td colspan="8">
                                    <table class="table table-sm table-bordered mb-3 text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-th align-middle" rowspan="3">Mesin / Peralatan</th>
                                                <th class="border-th align-middle" rowspan="3">Jenis Perbaikan</th>
                                                <th class="border-th align-middle" rowspan="3">Area</th>
                                                <th class="border-th" colspan="4">Kondisi Mesin Setelah Perbaikan</th>
                                                <th class="border-th align-middle" rowspan="3">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <th class="border-th" colspan="2">Kebersihan</th>
                                                <th class="border-th" colspan="2">Spare Part yang</th>
                                            </tr>
                                            <tr>
                                                <th class="border-th">Bersih</th>
                                                <th class="border-th">Kotor</th>
                                                <th class="border-th">Ada</th>
                                                <th class="border-th">Tidak Ada</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($report->details as $detail)
                                                <tr>
                                                    <td>{{ $detail->equipment->name ?? '-' }}</td>
                                                    <td>{{ $detail->repair_type ?? '-' }}</td>
                                                    <td>{{ $detail->section->section_name ?? '-' }}</td>
                                                    <td class="text-center">
                                                        {!! $detail->clean_condition === 'bersih' ? '✓' : '' !!}
                                                    </td>
                                                    <td class="text-center">
                                                        {!! $detail->clean_condition === 'kotor' ? 'X' : '' !!}
                                                    </td>
                                                    <td class="text-center">
                                                        {!! $detail->spare_part_left === 'ada' ? '✓' : '' !!}
                                                    </td>
                                                    <td class="text-center">
                                                        {!! $detail->spare_part_left === 'tidak ada' ? 'X' : '' !!}
                                                    </td>
                                                    <td>{{ $detail->notes ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('repair-cleanliness.add-detail', $report->uuid) }}" class="btn btn-sm btn-primary">+ Tambah Detail</a>
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
    $(document).ready(function () {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);

        $('.toggle-detail').on('click', function () {
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
