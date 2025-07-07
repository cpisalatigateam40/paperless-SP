@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Verifikasi Repack Produk</h4>
            <a href="{{ route('report_repack_verifs.create') }}" class="btn btn-sm btn-primary">+ Tambah Laporan</a>
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
                        <th>Area</th>
                        <th>Dibuat Oleh</th>
                        <th>Jumlah Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>{{ $report->details->count() }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_repack_verifs.destroy', $report->id) }}" method="POST"
                                onsubmit="return confirm('Delete this report?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_repack_verifs.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_repack_verifs.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>
                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="5">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Nama Produk</th>
                                            <th>Kode Produksi</th>
                                            <th>Expired Date</th>
                                            <th>Alasan Repack</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->details as $j => $detail)
                                        <tr>
                                            <td class="text-center">{{ $j + 1 }}</td>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->production_code }}</td>
                                            <td>{{ $detail->expired_date }}</td>
                                            <td>{{ $detail->reason }}</td>
                                            <td>{{ $detail->notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('report_repack_verifs.details.create', $report->uuid) }}"
                                        class="btn btn-sm btn-secondary mt-2">
                                        + Tambah Detail
                                    </a>
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