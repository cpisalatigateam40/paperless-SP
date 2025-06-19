@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Daftar Laporan Verifikasi Pembuatan Larutan</h5>
            <a href="{{ route('report-solvents.create') }}" class="btn btn-sm btn-primary">+ Tambah Laporan</a>
        </div>

        <div class="card-body table-responsive">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $i => $report)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}">Lihat Detail</button>

                                <form action="{{ route('report-solvents.destroy', $report->uuid) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>

                                <a href="{{ route('report-solvents.export-pdf', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    🖨 Cetak PDF
                                </a>

                                @can('approve report')
                                @if(!$report->approved_by)
                                    <form action="{{ route('report-solvents.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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

                        {{-- Detail Bahan --}}
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="6">
                                <table class="table table-sm table-bordered mb-0 align-middle text-center">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">No.</th>
                                            <th rowspan="2" class="align-middle">Nama Bahan</th>
                                            <th rowspan="2" class="align-middle">Kadar Yang Diinginkan</th>
                                            <th colspan="2" class="align-middle">Verifikasi Formulasi</th>
                                            <th rowspan="2" class="align-middle">Keterangan</th>
                                            <th rowspan="2" class="align-middle">Hasil Verifikasi</th>
                                            <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                            <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Volume Bahan (mL)</th>
                                            <th class="align-middle">Volume Larutan (mL)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($report->details as $j => $detail)
                                            <tr>
                                                <td>{{ $j + 1 }}</td>
                                                <td class="text-start">{{ $detail->solvent->name ?? '-' }}</td>
                                                <td>{{ $detail->solvent->concentration ?? '-' }}</td>
                                                <td>{{ $detail->solvent->volume_material ?? '-' }}</td>
                                                <td>{{ $detail->solvent->volume_solvent ?? '-' }}</td>
                                                <td class="text-start">{{ $detail->solvent->application_area ?? '-' }}</td>
                                                <td>{!! $detail->verification_result ? '✓' : '' !!}</td>
                                                <td>{{ $detail->corrective_action ?? '-' }}</td>
                                                <td>{{ $detail->reverification_action ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada laporan</td>
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
    $(document).ready(function () {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
        }, 3000);

        $('.toggle-detail').on('click', function () {
            const target = $(this.dataset.target);
            const isHidden = target.hasClass('d-none');

            // Tutup semua
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
