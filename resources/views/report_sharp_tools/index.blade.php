@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Laporan Pemeriksaan Benda Tajam</h5>
            <a href="{{ route('report_sharp_tools.create') }}" class="btn btn-primary btn-sm">Tambah</a>
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
                        <th class="align-middle text-center">No.</th>
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">Shift</th>
                        <th class="align-middle">Area</th>
                        <th class="align-middle">Dibuat Oleh</th>
                        <th class="align-middle text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td class="align-middle text-center">{{ $loop->iteration }}</td>
                        <td class="align-middle">{{ $report->date }}</td>
                        <td class="align-middle">{{ $report->shift }}</td>
                        <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                        <td class="align-middle">{{ $report->created_by }}</td>
                        <td class="d-flex align-items-center" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm toggle-detail" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Update --}}
                            <a href="{{ route('report_sharp_tools.edit', $report->uuid) }}"
                                class="btn btn-warning btn-sm" title="Update">
                                <i class="fas fa-pen"></i>
                            </a>

                            {{-- Hapus --}}
                            <form action="{{ route('report_sharp_tools.destroy', $report->uuid) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin hapus laporan?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report_sharp_tools.known', $report->id) }}" method="POST"
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
                            <form action="{{ route('report_sharp_tools.approve', $report->id) }}" method="POST"
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
                            <a href="{{ route('report_sharp_tools.exportPdf', $report->uuid) }}" target="_blank"
                                class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>

                    <tr class="detail-row d-none">
                        <td colspan="6">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Nama Alat</th>
                                        <th class="align-middle text-center">Jumlah Awal</th>
                                        <th class="align-middle text-center">Jumlah Akhir</th>
                                        <th class="align-middle text-center">Jam 1</th>
                                        <th class="align-middle text-center">Kondisi 1</th>
                                        <th class="align-middle text-center">Jam 2</th>
                                        <th class="align-middle text-center">Kondisi 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->details as $detail)
                                    <tr>
                                        <td class="align-middle">{{ $detail->sharpTool->name ?? '-' }}</td>
                                        <td class="align-middle text-center">{{ $detail->qty_start }}</td>
                                        <td class="align-middle text-center">{{ $detail->qty_end }}</td>
                                        <td class="align-middle text-center">{{ $detail->check_time_1 }}</td>
                                        <td class="align-middle text-center">{{ $detail->condition_1 }}</td>
                                        <td class="align-middle text-center">{{ $detail->check_time_2 }}</td>
                                        <td class="align-middle text-center">{{ $detail->condition_2 }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada detail.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="mt-2 d-flex justify-content-end">
                                <a href="{{ route('report_sharp_tools.details.add', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    + Tambah Baris Pemeriksaan
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