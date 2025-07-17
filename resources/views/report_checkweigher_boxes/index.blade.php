@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Pemeriksaan Checkweigher Box</h4>
            <a href="{{ route('report_checkweigher_boxes.create') }}" class="btn btn-primary btn-sm">
                Tambah Laporan
            </a>
        </div>
        <div class="card-body table-responsive">
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
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat Oleh</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $i => $report)
                    <tr>
                        <td>{{ $i + $reports->firstItem() }}</td>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-sm btn-info" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_checkweigher_boxes.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus></button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_checkweigher_boxes.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_checkweigher_boxes.export', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>

                        </td>
                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="100%">
                            <div class="table-responsive p-2 border rounded">
                                <table class="table table-bordered text-center table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">Waktu Pengecekan</th>
                                            <th rowspan="2" class="align-middle">Nama Produk</th>
                                            <th rowspan="2" class="align-middle">Kode Produksi</th>
                                            <th rowspan="2" class="align-middle">Expired Date</th>
                                            <th rowspan="2" class="align-middle">No Program</th>
                                            <th colspan="2" class="align-middle">Verifikasi Berat Checkweigher</th>
                                            <th colspan="3" class="align-middle">Verifikasi Fungsi Rejector</th>
                                            <th rowspan="2" class="align-middle">Tindakan Perbaikan</th>
                                            <th rowspan="2" class="align-middle">Verifikasi setelah perbaikan</th>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Checkweigher (gr)</th>
                                            <th class="align-middle">Manual (gr)</th>
                                            <th class="align-middle">Double Item</th>
                                            <th class="align-middle">Berat Kurang (Under)</th>
                                            <th class="align-middle">Berat Lebih (Over)</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($report->details as $detail)
                                        <tr>
                                            <td>{{ $detail->time_inspection ?? '-' }}</td>
                                            <td>{{ $detail->product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->production_code ?? '-' }}</td>
                                            <td>{{ $detail->expired_date ?? '-' }}</td>
                                            <td>{{ $detail->program_number ?? '-' }}</td>
                                            <td>{{ $detail->checkweigher_weight_gr ?? '-' }}</td>
                                            <td>{{ $detail->manual_weight_gr ?? '-' }}</td>
                                            <td>{{ $detail->double_item ? '✓' : '-' }}</td>
                                            <td>{{ $detail->weight_under ? '✓' : '-' }}</td>
                                            <td>{{ $detail->weight_over ? '✓' : '-' }}</td>
                                            <td>{{ $detail->corrective_action ?? '-' }}</td>
                                            <td>{{ $detail->verification ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('report_checkweigher_boxes.add-detail', $report->uuid) }}"
                                        class="btn btn-sm btn-secondary mt-2">
                                        Tambah Detail
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="6">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $reports->links() }}
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