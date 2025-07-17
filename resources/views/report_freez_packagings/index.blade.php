@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Pembekuan IQF & Pengemasan Karton Box</h4>
            <a href="{{ route('report_freez_packagings.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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
                <thead class="table-light">
                    <tr>
                        <th>No</th>
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
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#detail-{{ $report->id }}">
                                Lihat Detail
                            </button>

                            <form action="{{ route('report_freez_packagings.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_freez_packagings.approve', $report->id) }}" method="POST"
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

                            <a href="{{ route('report_freez_packagings.export_pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">
                                🖨 Cetak PDF
                            </a>

                        </td>
                    </tr>
                    <tr class="collapse" id="detail-{{ $report->id }}">
                        <td colspan="100%">
                            <div class="table-responsive p-2 border rounded">
                                <table class="table table-bordered text-center table-sm align-middle"
                                    style="font-size: 13px;">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Waktu Pemeriksaan</th>
                                            <th rowspan="2">Nama Produk</th>
                                            <th rowspan="2">Kode Produksi</th>
                                            <th rowspan="2">Best Before</th>

                                            <th colspan="6">PEMBEKUAN</th>
                                            <th colspan="6">KARTONING</th>
                                        </tr>
                                        <tr>
                                            <th>Suhu Produk Awal</th>
                                            <th>Suhu Produk Akhir</th>
                                            <th>Suhu IQF Room</th>
                                            <th>Suhu IQF Suction</th>
                                            <th>Lama Pembekuan Display</th>
                                            <th>Lama Pembekuan Aktual</th>

                                            <th>Kode Karton</th>
                                            <th>Isi Bag</th>
                                            <th>Isi Binded</th>
                                            <th>Berat Standar (kg)</th>
                                            <th>Berat Aktual (kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->details as $detail)
                                        <tr>
                                            <td class="align-middle">
                                                {{ $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('H:i') : '-' }}
                                                -
                                                {{ $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('H:i') : '-' }}
                                            </td>
                                            <td class="align-middle">{{ $detail->product->product_name ?? '-' }}</td>
                                            <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>
                                            <td class="align-middle">{{ $detail->best_before ?? '-' }}</td>

                                            {{-- Freezing --}}
                                            <td class="align-middle">{{ $detail->freezing->start_product_temp ?? '-' }}
                                            </td>
                                            <td>{{ $detail->freezing->end_product_temp ?? '-' }}</td>
                                            <td>{{ $detail->freezing->iqf_room_temp ?? '-' }}</td>
                                            <td>{{ $detail->freezing->iqf_suction_temp ?? '-' }}</td>
                                            <td>{{ $detail->freezing->freezing_time_display ?? '-' }}</td>
                                            <td>{{ $detail->freezing->freezing_time_actual ?? '-' }}</td>

                                            {{-- Kartoning --}}
                                            <td>{{ $detail->kartoning->carton_code ?? '-' }}</td>
                                            <td>{{ $detail->kartoning->content_bag ?? '-' }}</td>
                                            <td>{{ $detail->kartoning->content_binded ?? '-' }}</td>
                                            <td>{{ $detail->kartoning->carton_weight_standard ?? '-' }}</td>
                                            <td>{{ $detail->kartoning->carton_weight_actual ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('report_freez_packagings.add-detail', $report->uuid) }}"
                                        class="btn btn-sm btn-secondary mt-2">
                                        Tambah Detail
                                    </a>
                                </div>

                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
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