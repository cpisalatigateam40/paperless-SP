@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Laporan Pemeriksaan Timbangan & Thermometer</h5>
            <a href="{{ route('report-scales.create') }}" class="btn btn-primary btn-sm">+ Tambah Laporan</a>
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
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                <td>{{ $report->shift }}</td>
                                <td>{{ $report->area->name ?? '-' }}</td>
                                <td>{{ $report->created_by }}</td>
                                <td>
                                    <a href="{{ route('report-scales.edit', $report->uuid) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('report-scales.destroy', $report->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>

                                    <a href="{{ route('report-scales.export-pdf', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                                    @can('approve report')
                                    @if(!$report->approved_by)
                                        <form action="{{ route('report-scales.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
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
                                <td colspan="5" class="text-center">Belum ada laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $reports->links('pagination::bootstrap-5') }}
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
    });
    </script>
@endsection
