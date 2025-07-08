@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Retur</h4>
            <a href="{{ route('report_returns.create') }}" class="btn btn-sm btn-primary">+ Tambah Report</a>
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
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat oleh</th>
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
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <a href="{{ route('report_returns.export_pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_returns.approve', $report->id) }}" method="POST"
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

                            <form action="{{ route('report_returns.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus report ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="6">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Bahan Baku</th>
                                            <th>Supplier</th>
                                            <th>Kode Produksi</th>
                                            <th>Jumlah</th>
                                            <th>Alasan Hold</th>
                                            <th>Tindak Lanjut</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->details as $j => $detail)
                                        <tr>
                                            <td class="text-center">{{ $j + 1 }}</td>
                                            <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
                                            <td>{{ $detail->supplier }}</td>
                                            <td>{{ $detail->production_code }}</td>
                                            <td>{{ $detail->quantity }} {{ $detail->unit }}</td>
                                            <td>{{ $detail->hold_reason }}</td>
                                            <td>{{ $detail->action }}</td>
                                            <td class="text-center">
                                                <form
                                                    action="{{ route('report_returns.details.destroy', $detail->id) }}"
                                                    method="POST" onsubmit="return confirm('Hapus detail ini?')"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">🗑</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_returns.details.create', $report->uuid) }}"
                                        class="btn btn-sm btn-secondary">+ Tambah Detail</a>
                                </div>
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
</script>
@endsection