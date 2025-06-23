@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Daftar Pemeriksaan Magnet Trap</h5>
            <a href="{{ route('report_magnet_traps.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">Area</th>
                        <th class="align-middle">Section</th>
                        <th class="align-middle">Shift</th>
                        <th class="align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td>{{ $report->shift }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-sm btn-info toggle-detail">Lihat Detail</button>

                            <form action="{{ route('report_magnet_traps.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_magnet_traps.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_magnet_traps.exportPdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>
                        </td>
                    </tr>

                    <tr class="detail-row d-none">
                        <td colspan="5">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Shift</th>
                                        <th class="align-middle">Jam</th>
                                        <th class="align-middle">Temuan</th>
                                        <th class="align-middle">QC</th>
                                        <th class="align-middle">Produksi</th>
                                        <th class="align-middle">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report->details as $detail)
                                    <tr>
                                        <td>{{ $report->shift }}</td>
                                        <td>{{ $detail->time }}</td>
                                        <td>{{ $detail->finding }}</td>
                                        <td class="text-center">
                                            @if ($detail->source === 'QC')
                                            ✓
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($detail->source === 'Produksi')
                                            ✓
                                            @endif
                                        </td>
                                        <td>{{ $detail->note ?: '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada detail.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-2 d-flex justify-content-end">
                                <a href="{{ route('report_magnet_traps.details.add', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    + Tambah Detail Pemeriksaan
                                </a>
                            </div>
                        </td>
                    </tr>


                    @endforeach
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
});

document.querySelectorAll('.toggle-detail').forEach((button) => {
    button.addEventListener('click', function() {
        const detailRow = this.closest('tr').nextElementSibling;
        detailRow.classList.toggle('d-none');
        this.textContent = detailRow.classList.contains('d-none') ? 'Lihat Detail' :
            'Sembunyikan Detail';
    });
});
</script>
@endsection