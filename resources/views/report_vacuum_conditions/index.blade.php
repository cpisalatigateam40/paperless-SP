@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Vacuum Condition</h4>
            <a href="{{ route('report_vacuum_conditions.create') }}" class="btn btn-primary btn-sm">Tambah Report</a>
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
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area?->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_vacuum_conditions.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus report ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_vacuum_conditions.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_vacuum_conditions.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">🖨 Cetak PDF</a>
                        </td>
                    </tr>

                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="5">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Jam</th>
                                            <th>Kode Produksi</th>
                                            <th>Expired Date</th>
                                            <th>Jumlah Pack</th>
                                            <th>Seal Bocor</th>
                                            <th>Melipat Bocor</th>
                                            <th>Casing Bocor</th>
                                            <th>Lain-lain</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->details as $j => $detail)
                                        <tr>
                                            <td class="text-center">{{ $j + 1 }}</td>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->time }}</td>
                                            <td>{{ $detail->production_code }}</td>
                                            <td>{{ $detail->expired_date }}</td>
                                            <td class="text-center">{{ $detail->pack_quantity }}</td>
                                            <td class="text-center">{{ $detail->leaking_area_seal ? '✔' : '-' }}</td>
                                            <td class="text-center">{{ $detail->leaking_area_melipat ? '✔' : '-' }}</td>
                                            <td class="text-center">{{ $detail->leaking_area_casing ? '✔' : '-' }}</td>
                                            <td>{{ $detail->leaking_area_other }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-end mt-2">
                                    <a href="{{ route('report_vacuum_conditions.details.create', $report->uuid) }}"
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