@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan Inventaris QC</h5>
                <a href="{{ route('report-qc-equipment.create') }}" class="btn btn-primary btn-sm">+ Buat Laporan
                    Baru</a>
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
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}">
                                    Lihat Detail
                                </button>

                                <a href="{{ route('report-qc-equipment.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning">Update Laporan</a>

                                <form action="{{ route('report-qc-equipment.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>

                                <a href="{{ route('report-qc-equipment.export', $report->uuid) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                                @can('approve report')
                                @if(!$report->approved_by)
                                <form action="{{ route('report-qc-equipment.approve', $report->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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
                            </td>
                        </tr>
                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="4">
                                @php
                                $groupedDetails = $report->details->groupBy(fn($d) => $d->qcEquipment->section_name ??
                                'Lainnya');
                                $no = 1;
                                @endphp

                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle border-th">No</th>
                                            <th rowspan="2" class="text-center align-middle border-th">Nama Alat</th>
                                            <th rowspan="2" class="text-center align-middle border-th">Jumlah</th>
                                            <th colspan="2" class="text-center border-th">Kondisi</th>
                                            <th rowspan="2" class="text-center align-middle border-th">Keterangan</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center border-th">Awal Shift*</th>
                                            <th class="text-center border-th">Akhir Shift*</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $grouped = $report->details->groupBy(fn($item) => $item->item->section_name);
                                        $no = 1;

                                        $notesOptions = [
                                        '-' => 'Tidak Tersedia',
                                        '1' => 'Baik',
                                        '2' => 'Rusak',
                                        '3' => 'Hilang',
                                        '4' => 'Bersih',
                                        '5' => 'Kotor',
                                        '6' => 'Masih',
                                        '7' => 'Habis',
                                        '8' => 'Di dalam meja',
                                        '9' => 'Di luar meja',
                                        '10' => 'Baik, Bersih, Masih, Di dalam meja',
                                        ];
                                        @endphp

                                        @foreach ($grouped as $section => $items)
                                        <tr class="section-row">
                                            <td colspan="6"><strong>{{ strtoupper($section) }}</strong></td>
                                        </tr>
                                        @foreach ($items as $item)
                                        <tr>
                                            <td class="text-center">{{ $no++ }}</td>
                                            <td>{{ $item->item->item_name }}</td>
                                            <td class="text-center">{{ $item->item->quantity }}</td>
                                            <td class="text-center">{{ $item->time_start == '1' ? '✓' : 'X' }}</td>
                                            <td class="text-center">{{ $item->time_end == '1' ? '✓' : '' }}</td>
                                            <td>{{ $notesOptions[$item->notes] ?? '-' }}</td>
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

    $(document).ready(function() {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);

        // Toggle Detail
        $('.toggle-detail').on('click', function() {
            const target = $(this.dataset.target);
            const isHidden = target.hasClass('d-none');

            $('.toggle-detail').not(this).text('Lihat Detail'); // reset semua label
            $('tr[id^="detail-"]').addClass('d-none'); // sembunyikan semua detail

            if (isHidden) {
                target.removeClass('d-none');
                $(this).text('Sembunyikan Detail');
            } else {
                target.addClass('d-none');
                $(this).text('Lihat Detail');
            }
        });
    });
});
</script>
@endsection