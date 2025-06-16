@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan Inventaris QC</h5>
                <a href="{{ route('report-qc-equipment.create') }}" class="btn btn-primary btn-sm">+ Buat Laporan Baru</a>
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
                                    <a href="{{ route('report-qc-equipment.edit', $report->uuid) }}" class="btn btn-sm btn-warning">Update Laporan</a>
                                    
                                    <form action="{{ route('report-qc-equipment.destroy', $report->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>

                                    <a href="{{ route('report-qc-equipment.export', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                                    @can('approve report')
                                    @if(!$report->approved_by)
                                        <form action="{{ route('report-qc-equipment.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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
    });
    </script>
@endsection
