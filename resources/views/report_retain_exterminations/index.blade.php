@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Pemusnahan Retain Sample</h4>
            <a href="{{ route('report_retain_exterminations.create') }}" class="btn btn-primary btn-sm">Tambah
                Report</a>
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
                        <th>Jumlah Detail</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->details_count }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button type="button" class="btn btn-info btn-sm"
                                onclick="toggleDetail('{{ $report->uuid }}')">Lihat Detail</button>

                            <form method="POST"
                                action="{{ route('report_retain_exterminations.destroy', $report->uuid) }}">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin hapus?')"
                                    class="btn btn-danger btn-sm">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_retain_exterminations.approve', $report->id) }}"
                                method="POST" style="display:inline-block;"
                                onsubmit="return confirm('Setujui laporan ini?')">
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

                            <a href="{{ route('report_retain_exterminations.export-pdf', $report->uuid) }}"
                                class="btn btn-outline-secondary btn-sm" target="_blank">🖨 Cetak PDF</a>

                        </td>
                    </tr>

                    <tr id="detail-{{ $report->uuid }}" style="display: none;">
                        <td colspan="5">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Retain</th>
                                        <th>Exp Date</th>
                                        <th>Kondisi</th>
                                        <th>Jumlah</th>
                                        <th>Jumlah Kg</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($report->details as $detail)
                                    <tr>
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td>{{ $detail->retain_name }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($detail->exp_date)->format('d-m-Y') }}</td>
                                        <td>{{ $detail->retain_condition }}</td>
                                        <td class="text-center">{{ $detail->quantity }} {{ $detail->shape }}</td>
                                        <td class="text-center">{{ $detail->quantity_kg }}</td>
                                        <td class="text-center">{{ $detail->notes }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end mb-2 mt-2">
                                <a href="{{ route('report_retain_exterminations.add-detail', $report->uuid) }}"
                                    class="btn btn-secondary btn-sm">Tambah Detail</a>
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

function toggleDetail(uuid) {
    let row = document.getElementById('detail-' + uuid);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>
@endsection